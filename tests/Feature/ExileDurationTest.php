<?php

namespace Tests\Feature;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Support\ExileDuration;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Locks in the exile-counter fix: a prisoner's exile time is the union of
 * their cases' exile spans, not the sum of the per-case day counts.
 *
 * The bug in public: William Morales carried two case rows with an
 * in_exile_since — the 1979 Bellevue escape and the 1988 release from Mexican
 * custody to Cuba. Both were open-ended, so both counted through to today, and
 * his profile rendered "Time in Exile: 85 Years 3 Months 21 Days" (47 + 38) for
 * a man exiled since 1988.
 */
class ExileDurationTest extends TestCase
{
    use RefreshDatabase;

    private function exile(): Prisoner
    {
        return Prisoner::create([
            'name' => 'Test Exile',
            'currently_in_exile' => true,
            'in_exile' => true,
        ]);
    }

    public function test_overlapping_open_ended_exiles_are_counted_once(): void
    {
        $prisoner = $this->exile();

        // Explicit in_exile_since on both, so the auto-derive branch in
        // PrisonerCase::saving() plays no part in what is being measured.
        PrisonerCase::create([
            'prisoner_id' => $prisoner->id,
            'in_exile_since' => '1979-05-21',
        ]);
        PrisonerCase::create([
            'prisoner_id' => $prisoner->id,
            'in_exile_since' => '1988-06-24',
        ]);

        $cases = $prisoner->refresh()->cases;

        // Both rows run to today, so the naive column sum is nearly double.
        $expected = (int) Carbon::parse('1979-05-21')->diffInDays(Carbon::today());

        $this->assertSame($expected, ExileDuration::totalDays($cases));
        $this->assertGreaterThan(
            ExileDuration::totalDays($cases),
            (int) $cases->sum('in_exile_for_days'),
        );
    }

    public function test_separate_exiles_with_a_gap_still_add_up(): void
    {
        $prisoner = $this->exile();

        PrisonerCase::create([
            'prisoner_id' => $prisoner->id,
            'in_exile_since' => '1970-01-01',
            'end_of_exile' => '1970-01-11',
        ]);
        PrisonerCase::create([
            'prisoner_id' => $prisoner->id,
            'in_exile_since' => '1980-01-01',
            'end_of_exile' => '1980-01-06',
        ]);

        $this->assertSame(15, ExileDuration::totalDays($prisoner->refresh()->cases));
    }

    public function test_start_anchors_on_the_earliest_counted_exile(): void
    {
        $prisoner = $this->exile();

        // Ends before it begins — computeInExileForDays() stores null, so the
        // row contributes nothing and must not anchor the breakdown either.
        PrisonerCase::create([
            'prisoner_id' => $prisoner->id,
            'in_exile_since' => '1960-01-01',
            'end_of_exile' => '1959-01-01',
        ]);
        PrisonerCase::create([
            'prisoner_id' => $prisoner->id,
            'in_exile_since' => '1988-06-24',
            'end_of_exile' => '1988-07-04',
        ]);

        $cases = $prisoner->refresh()->cases;

        $this->assertSame(10, ExileDuration::totalDays($cases));
        $this->assertSame('1988-06-24', ExileDuration::startFor($cases)?->format('Y-m-d'));
    }

    public function test_no_exile_rows_total_zero_and_have_no_start(): void
    {
        $prisoner = $this->exile();

        PrisonerCase::create([
            'prisoner_id' => $prisoner->id,
            'incarceration_date' => '1990-01-01',
            'release_date' => '1990-02-01',
        ]);

        // The release above is the prisoner's last, so the auto-derive branch
        // legitimately reads it as the start of their exile.
        $prisoner->refresh();
        $this->assertNotNull($prisoner->cases->first()->in_exile_since);

        $bare = Prisoner::create(['name' => 'Never Exiled']);
        PrisonerCase::create([
            'prisoner_id' => $bare->id,
            'incarceration_date' => '1990-01-01',
            'release_date' => '1990-02-01',
        ]);

        $this->assertSame(0, ExileDuration::totalDays($bare->refresh()->cases));
        $this->assertNull(ExileDuration::startFor($bare->cases));
    }

    public function test_release_followed_by_later_custody_is_not_an_exile_start(): void
    {
        $prisoner = $this->exile();

        // The escape: custody ends 1979-05-21 with no other column to record
        // it in. Saved first, so it cannot see the sibling row yet.
        $escape = PrisonerCase::create([
            'prisoner_id' => $prisoner->id,
            'incarceration_date' => '1978-07-12',
            'release_date' => '1979-05-21',
        ]);

        $recapture = PrisonerCase::create([
            'prisoner_id' => $prisoner->id,
            'incarceration_date' => '1983-05-26',
            'release_date' => '1988-06-24',
        ]);

        // Re-saving the escape row — an admin edit, or the recompute command —
        // must not resurrect the exile date now that the later custody exists.
        $escape->in_exile_since = null;
        $escape->save();

        $this->assertNull($escape->refresh()->in_exile_since);
        $this->assertSame('1988-06-24', $recapture->refresh()->in_exile_since?->format('Y-m-d'));

        $expected = (int) Carbon::parse('1988-06-24')->diffInDays(Carbon::today());
        $this->assertSame($expected, ExileDuration::totalDays($prisoner->refresh()->cases));
    }
}
