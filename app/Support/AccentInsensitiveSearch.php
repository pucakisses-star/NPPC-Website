<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Accent-insensitive LIKE matching, so "Maria Cueto" finds "María Cueto"
 * (and vice versa) without the searcher having to type diacritics.
 *
 * On MySQL the comparison is forced through an accent-insensitive collation
 * (utf8mb4_unicode_ci folds á→a, ñ→n, ü→u, ç→c …), which handles matching in
 * both directions regardless of how the column itself is collated. Other
 * drivers (sqlite in tests) have no equivalent collation, so they fall back to
 * a plain case-insensitive LIKE.
 */
final class AccentInsensitiveSearch
{
    /** MySQL collation that ignores both case and accents. */
    private const COLLATION = 'utf8mb4_unicode_ci';

    /**
     * Add a "column LIKE %term%" constraint that ignores accents.
     *
     * @param  bool  $or  chain with orWhere instead of where
     */
    public static function like(Builder $query, string $column, string $term, bool $or = false): Builder
    {
        $grammar = $query->getQuery()->getGrammar();
        $wrapped = $grammar->wrap($column);
        $binding = '%'.$term.'%';

        $sql = $query->getConnection()->getDriverName() === 'mysql'
            ? sprintf('%s COLLATE %s LIKE ?', $wrapped, self::COLLATION)
            : sprintf('LOWER(%s) LIKE LOWER(?)', $wrapped);

        return $or
            ? $query->orWhereRaw($sql, [$binding])
            : $query->whereRaw($sql, [$binding]);
    }

    /**
     * Add an accent-insensitive LIKE across several columns, OR-ed together
     * and wrapped in their own group so surrounding constraints still apply.
     *
     * @param  string[]  $columns
     */
    public static function likeAny(Builder $query, array $columns, string $term): Builder
    {
        return $query->where(function (Builder $inner) use ($columns, $term) {
            foreach ($columns as $i => $column) {
                self::like($inner, $column, $term, $i > 0);
            }
        });
    }

    /**
     * Match every word of the search term, each in any of the given columns,
     * so a search does not have to be one contiguous substring: "Joseph
     * Smith" finds "Joseph William Smith", and "cueto maria" finds "María
     * Cueto" whatever the word order.
     *
     * Words are AND-ed (every word must appear somewhere) while columns are
     * OR-ed per word, and the whole thing is wrapped in one group so
     * surrounding constraints still apply.
     *
     * @param  string[]  $columns
     */
    public static function allWords(Builder $query, array $columns, string $term): Builder
    {
        $words = preg_split('/\s+/', trim($term), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (! $words) {
            return $query;
        }

        return $query->where(function (Builder $outer) use ($columns, $words) {
            foreach ($words as $word) {
                $outer->where(fn (Builder $inner) => self::likeAny($inner, $columns, $word));
            }
        });
    }
}
