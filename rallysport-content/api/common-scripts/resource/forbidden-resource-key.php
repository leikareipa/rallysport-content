<?php namespace RSC\Resource;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content (RSC)
 * 
 */

// When generating a new resource ID (e.g. "track.xxx-xxx-xxx"), match its key
// element (e.g. "xxx-xxx-xxx") against this list of forbidden words - if the
// key contains any of these words, the ID should be discarded and re-generated.
//
// Note that although the key element might only contain three characters/letters
// per segment (e.g. "abc-def-ghi" has the three segments "abc", "def", and
// "ghi"), words longer than three characters can form when the segments are
// read together. For this reason, the ::matches() utility function is provided
// - simply call it with the key you want to test, and if the function returns
// false, the key does not contain any of the forbidden words.
//
abstract class ForbiddenResourceKey
{
    static public function matches(string $key) : bool
    {
        $concatenatedKey = str_replace(ResourceID::RESOURCE_KEY_FRAGMENT_SEPARATOR, "", $key);

        foreach (self::FORBIDDEN_WORDS as $forbiddenWord)
        {
            if (preg_match("/{$forbiddenWord}/i", $concatenatedKey))
            {
                return true;
            }
        }

        return false;
    }

    // The forbidden words, in regex format.
    public const FORBIDDEN_WORDS = [
        "f[a4][g9]",
        "[a4][s5][s5]",
        "fu[kg9qc]r?",
        "c[o0u][ckx]",
        "d[i1][kcx]",
        "p[e3]n[i1][s5]",
        "c[l1][t7]",
        "[g9][a4]y",
        "[g9][e3a4][i1y]",
        "ke[ij]",
        "h[o0]m[o0]",
        "f[a4]p",
        "tu[g9]",
        "j[e3]w",
        "j[o0][o0]",
        "juu",
        "tag",
        "p[o0][o0]",
        "[s5]h[i1][t7]",
        "p[i1][s5][s5]",
        "nu[t7]",
        "[g9][e3]n[i1]t[a4][l1]",
        "[s5][e3]m[e3]n",
        "[s5][t7]d",
        "[s5][e3]x",
        "[s5][e3]k[s5z2]",
        "[g9][i1][z2][z2]",
        "j[i1][z2][z2]",
        "[t7][i1][t7]",
        "[b6]r[e3][a4][s5][t7]",
        "n[a4]d",
        "v[a4]?[g9][i1]n[a4]",
        "v[g9]n",
        "v[a4]j",
        "c[l1][i1][t7]",
        "c[l1][t7]",
        "[o0]r[a4][l1]",
        "[a4]nu[s5]",
        "[a4]n[a4][l1]",
        "[a4]n[l1]",
        "[s5][ua4][ckg9]",
        "tup",
        "[ck]um",
        "[b68][i1][t7]ch",
        "wh[o0]r[e3]",
        "n[i1][g9]",
        "n[g9]r",
        "kkk",
        "c[o0]mm[i1][e3]",
        "n[a4][z2][i1]",
        "911",
        "112",
        "666",
        "vv",
    ];
}
