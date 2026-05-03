<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

class ExpandSeditionActBios extends Command
{
    protected $signature = 'prisoners:expand-sedition-bios {--dry-run : Print what would be appended without writing}';
    protected $description = 'Expand the bios of the 13 1798 Sedition Act-era editor prisoners by appending researched context. Existing description is preserved.';

    /**
     * For each prisoner: text to APPEND to the existing description
     * (separated from it by two newlines). Existing description is
     * never overwritten.
     */
    private const APPENDS = [
        'Franklin Bache' => <<<'TXT'
Bache's full name was Benjamin Franklin Bache (August 12, 1769 – September 10, 1798). He was the grandson and namesake of Benjamin Franklin and was raised in his grandfather's household in Passy outside Paris during Franklin's diplomatic service to the new United States. After returning to Philadelphia he founded the General Advertiser in 1790, renamed the Aurora General Advertiser in 1794, which quickly became the most influential Democratic-Republican (Jeffersonian) newspaper in the country. Through the 1790s Bache used the Aurora to attack Washington's Federalist administration, the Jay Treaty, and the British orientation of U.S. foreign policy.

In June 1798, weeks before Congress passed the Sedition Act, federal prosecutors indicted him under the common law of seditious libel for printing what they characterized as malicious falsehoods against President John Adams and the executive branch. He was arrested and released on \$2,000 bond. He died of yellow fever in Philadelphia on September 10, 1798, before he could be tried. His widow Margaret Hartman Markoe Bache continued to publish the Aurora; she later married William Duane, his successor at the paper.
TXT,

        'William Duane' => <<<'TXT'
William Duane (May 17, 1760 – November 24, 1835) was an Irish-American printer and editor who succeeded Benjamin Franklin Bache at the Philadelphia Aurora after Bache's death in 1798. He married Bache's widow Margaret Hartman Markoe Bache in 1800. Born to Irish Catholic parents in upstate New York, Duane had previously been deported from British India for press criticism of the East India Company, then expelled from the United Kingdom for his journalism, before settling in Philadelphia in 1796.

Duane was indicted at least three separate times in connection with his editorship of the Aurora. The most significant was a July 1799 prosecution under the Sedition Act for printing a story alleging that Federalist senators were corruptly influenced by Britain. A subsequent 1800 prosecution for breach of Senate privilege drove him into hiding for months; he was indicted again under the Sedition Act in October 1800 over a story alleging Federalist plans to disenfranchise voters. The cases were dropped after Thomas Jefferson took office in March 1801 and let the Sedition Act expire. Duane went on to serve as a U.S. Army adjutant general during the War of 1812 and continued editing the Aurora into the 1820s.
TXT,

        'Thomas Adams' => <<<'TXT'
Thomas Adams (c. 1757 – 1799) was the editor and proprietor of the Boston Independent Chronicle, the leading Democratic-Republican newspaper in New England, which he had taken over from his father-in-law Thomas Edes. The Chronicle was one of the most influential opposition voices outside Philadelphia and a frequent target of Federalist outrage.

In October 1798, Adams was indicted by a federal grand jury in Boston for common-law seditious libel — the same charge brought against Benjamin Franklin Bache months earlier — and again indicted under the new Sedition Act for publishing material attacking President Adams's administration. He was already gravely ill at the time of indictment and died in 1799 before his case could be tried. After his death his brother Abijah Adams, who had served as the paper's bookkeeper, was prosecuted in his place by Massachusetts state authorities.
TXT,

        'Abijah Adams' => <<<'TXT'
Abijah Adams was the brother of Thomas Adams, editor of the Boston Independent Chronicle, and the bookkeeper at the paper. After Thomas Adams's federal Sedition Act prosecution lapsed when he died in 1799, Massachusetts state authorities prosecuted Abijah for contempt of the Massachusetts General Court (the state legislature) over a Chronicle editorial that had accused the legislature of unconstitutional conduct in passing resolutions condemning Virginia and Kentucky for their opposition to the federal Sedition and Alien Acts.

Tried before Chief Justice Francis Dana of the Massachusetts Supreme Judicial Court in February 1799, he was convicted of contempt and sentenced to thirty days in the Boston jail plus \$5 in costs. The case has been described by historians as the only successful state-level common-law prosecution for criticizing a legislature in the Sedition Act period and is regarded as one of the most aggressive uses of judicial contempt against the press in early American history.
TXT,

        'Anthony Haswell' => <<<'TXT'
Anthony Haswell (April 6, 1756 – May 26, 1816) was an English-born printer and editor who emigrated to Massachusetts as a teenager, served briefly in the Continental Army during the Revolutionary War, and after the war moved to Bennington, Vermont, where he founded the Vermont Gazette in 1783. By the late 1790s the Gazette was the leading Democratic-Republican newspaper in Vermont and a vocal critic of the Federalist Adams administration.

In 1799 Haswell published an advertisement soliciting funds for the imprisoned Vermont congressman Matthew Lyon — already in his Sedition Act-driven federal prison sentence in Vergennes — that mocked the Adams administration's Federalist appointees. He was indicted under the Sedition Act in October 1799 and tried in Rutland in May 1800; the federal jury convicted him after a brief deliberation. Justice William Paterson of the U.S. Supreme Court, riding circuit, sentenced Haswell to two months in jail and a \$200 fine. He served his sentence at the Vergennes jail in Vermont. He continued publishing until his death in 1816.
TXT,

        'Thomas Cooper' => <<<'TXT'
Thomas Cooper (October 22, 1759 – May 11, 1839) was an English-born scientist, lawyer, and political radical who emigrated to the United States in 1794 after the British government drove him into exile for his support of the French Revolution. He settled in Northumberland County, Pennsylvania, where in 1799 he became editor of the Northumberland Gazette and a vocal critic of the Adams administration.

In October 1799 Cooper published a handbill attacking President Adams for misrepresenting his administration's foreign and military policies. He was indicted under the Sedition Act in April 1800 and tried before Justice Samuel Chase, riding circuit in Philadelphia. Chase's conduct of the trial — including direct hostility to Cooper from the bench — would later become one of the principal articles in the U.S. House's 1804 impeachment of Chase. The jury convicted Cooper, and he was sentenced to six months in the Philadelphia jail and a \$400 fine. After his release he continued his political journalism and became a major figure in early American science: he served as professor of chemistry at Dickinson College, then at the University of Pennsylvania, and finally as the second president of South Carolina College (now the University of South Carolina) from 1820 to 1834. He was a close personal friend and political ally of Thomas Jefferson.
TXT,

        'James Thompson Callender' => <<<'TXT'
James Thompson Callender (c. 1758 – July 17, 1803) was a Scottish-born journalist and pamphleteer who emigrated to Philadelphia in 1793 after being indicted in Edinburgh for sedition. In the United States he wrote for the Philadelphia Aurora and Richmond Examiner and produced a series of acid pamphlets attacking the Federalist Party. His 1797 pamphlet The History of the United States for 1796 first publicly exposed Treasury Secretary Alexander Hamilton's affair with Maria Reynolds and the hush-money payments to her husband.

In 1800 Callender published The Prospect Before Us, a long pamphlet attacking President John Adams. He was indicted under the Sedition Act in May 1800, tried before Justice Samuel Chase in Richmond — the same judge whose conduct of the Cooper trial weeks earlier would form part of his impeachment — and convicted. He was sentenced to nine months in the Richmond jail and a \$200 fine. President Jefferson pardoned him after taking office in March 1801. Embittered when Jefferson refused to appoint him postmaster of Richmond as a reward, Callender turned on his former patron: in September 1802 he published in the Richmond Recorder the first allegations that Jefferson had fathered children with the enslaved Sally Hemings. He drowned, drunk, in the James River in Richmond on July 17, 1803.
TXT,

        'Charles Holt' => <<<'TXT'
Charles Holt (August 9, 1772 – December 4, 1852) was an American printer and editor and the publisher of the Bee in New London, Connecticut, the leading Democratic-Republican newspaper in southeastern Connecticut. He had been a journeyman printer in New York before founding the Bee in 1797 at age 24 specifically as a Jeffersonian counter-weight to the Federalist Connecticut Gazette.

In May 1799 Holt published an editorial attacking the Federalist administration's military buildup against France, in particular the recruitment of a "standing army" he depicted as a tool to suppress political opposition at home. He was indicted under the Sedition Act in September 1799 and tried before Justice Bushrod Washington (nephew of George Washington) in Hartford in April 1800. The federal jury convicted him; he was sentenced to three months in the New London jail and a \$200 fine. After his release he continued the Bee for several more years and later moved to New York to edit the Columbian. Holt's case is one of the most cited Sedition Act prosecutions because the underlying editorial was specifically about whether peacetime conscription threatened civil liberties — a theme that has resurfaced in every American war debate since.
TXT,

        'David Frothingham' => <<<'TXT'
David Frothingham (c. 1765 – 1800) was a journeyman printer at the New York Argus, a Democratic-Republican newspaper owned and edited by Thomas Greenleaf and, after Greenleaf's death from yellow fever in 1798, by his widow Ann Greenleaf. In November 1799 the Argus reprinted material from Bache's Aurora alleging that Alexander Hamilton, then a former Treasury Secretary, had attempted to secretly purchase the Aurora to suppress its anti-Federalist editorials. Hamilton brought a personal libel complaint and the New York attorney general indicted Frothingham on common-law libel charges (rather than under the federal Sedition Act).

Frothingham was tried in New York Court of General Sessions in November 1799, convicted, and sentenced to four months in the New York City Bridewell jail and a \$100 fine. He was unable to pay the fine and remained imprisoned beyond the four-month sentence. He died in the Bridewell in 1800 — making him, with William 'Avalon' Rodgers in 2005, one of the small handful of American press defendants to die in custody before completing a sentence.
TXT,

        'Ann Greenleaf' => <<<'TXT'
Ann Greenleaf (c. 1768 – 1827) was a New York newspaper publisher and the widow of Thomas Greenleaf, who had founded the Argus and the Greenleaf's New Daily Advertiser as the principal Democratic-Republican papers in New York City. After Thomas Greenleaf's death in the 1798 yellow fever epidemic, Ann took over publication of both papers, becoming one of the very few women publishing a daily political newspaper in late-eighteenth-century America.

In late 1799 Federalist authorities indicted her under the Sedition Act over articles in the Argus that had reprinted Bache and Duane material attacking Alexander Hamilton and the Adams administration. She was arrested but the prosecution against her was suspended after she sold the Argus to David Denniston in early 1800. The case was finally dropped after Jefferson took office and the Sedition Act expired in March 1801. Her case is significant in U.S. press-freedom history as the only known Sedition Act prosecution of a woman publisher.
TXT,

        'John S. Lillie' => <<<'TXT'
John S. Lillie was the editor of the Boston Constitutional Telegraphe, a small Democratic-Republican weekly that had taken up the cause of Abijah Adams after Adams's 1799 conviction for contempt of the Massachusetts legislature. In late 1800 Lillie published commentary in the Telegraphe attacking the conduct of Chief Justice Francis Dana of the Massachusetts Supreme Judicial Court — the judge who had presided over Adams's trial — and accusing him of judicial bias.

Lillie was prosecuted by Dana himself for contempt of court in early 1801, convicted in summary proceedings without a jury, and sentenced to three months in the Boston jail. Like the Abijah Adams prosecution before him, the Lillie case was a state-level use of the contempt power to punish criticism of the judiciary, parallel to the federal Sedition Act prosecutions then winding down. The case was widely cited in the early-nineteenth-century debate over the limits of summary contempt against the press.
TXT,

        'Conrad Fahnestock' => <<<'TXT'
Conrad Fahnestock (also spelled Fahnstock) was the co-editor, with Benjamin Mayer, of the Harrisburger Morgenrothe, a Democratic-Republican German-language weekly newspaper published in Harrisburg, Pennsylvania. The Morgenrothe was one of several German-language Pennsylvania papers that played a major role in mobilizing the German-American Pennsylvania vote against the Federalist Adams administration; Pennsylvania's German-speaking communities were a key constituency in the 1800 election that brought Jefferson to power.

Fahnestock and Mayer were indicted under the Sedition Act in 1799 over Morgenrothe articles attacking the Federalists, with the prosecutions led by U.S. Attorney William Rawle in Philadelphia. Their cases were still pending when Jefferson took office in March 1801 and let the Sedition Act expire; the indictments against them were never tried. The Morgenrothe continued publication into the early 1810s.
TXT,

        'Benjamin Moye' => <<<'TXT'
The defendant generally appears in Sedition Act records as Benjamin Mayer (the German spelling Bayer/Mayer was sometimes anglicized as Moye/Moyer in court documents). He was the co-editor, with Conrad Fahnestock, of the Harrisburger Morgenrothe, the Democratic-Republican German-language weekly newspaper that played a significant role in turning Pennsylvania's German-speaking communities against the Federalist Adams administration in the 1800 election.

Mayer and Fahnestock were indicted under the Sedition Act in 1799 by U.S. Attorney William Rawle in Philadelphia over Morgenrothe articles attacking President Adams and Federalist policy. Like Fahnestock's case, Mayer's prosecution was still pending when Thomas Jefferson took office on March 4, 1801. With the Sedition Act expiring on the same day, the indictments against him were dropped without trial.
TXT,
    ];

    public function handle(): int
    {
        $updated = 0;
        $skippedNoPrisoner = 0;
        $skippedAlready = 0;

        foreach (self::APPENDS as $name => $append) {
            $prisoner = Prisoner::where('name', $name)->first();
            if (! $prisoner) {
                $this->error("  not found: {$name}");
                $skippedNoPrisoner++;
                continue;
            }

            $existing = (string) $prisoner->description;

            if (str_contains($existing, substr(trim($append), 0, 80))) {
                $this->line("  skip: {$name} already has the appended bio");
                $skippedAlready++;
                continue;
            }

            $separator = $existing === '' ? '' : "\n\n";
            $newDescription = $existing.$separator.trim($append);

            $charsBefore = strlen($existing);
            $charsAfter  = strlen($newDescription);

            $this->info("  {$name}: {$charsBefore} -> {$charsAfter} chars");

            if (! $this->option('dry-run')) {
                $prisoner->description = $newDescription;
                $prisoner->save();
            }

            $updated++;
        }

        $this->line('');
        if ($this->option('dry-run')) {
            $this->warn('Dry run — no changes written.');
        }
        $this->info("Done. Updated {$updated}; not found {$skippedNoPrisoner}; already appended {$skippedAlready}.");

        return self::SUCCESS;
    }
}
