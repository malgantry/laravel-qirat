<?php

namespace App\Helpers;

class ArabicShaper
{
    /*
     * Basic Arabic Glyph Map for Presentation Forms-B (U+FE70 - U+FEFF)
     * This maps the standard Unicode characters to their Isolated, Initial, Medial, and Final forms.
     */
    private static $glyphs = [
        'ء' => ['fe80', 'fe80', 'fe80', 'fe80'], 
        'آ' => ['fe81', 'fe82', 'fe81', 'fe82'],
        'أ' => ['fe83', 'fe84', 'fe83', 'fe84'],
        'ؤ' => ['fe85', 'fe86', 'fe85', 'fe86'],
        'إ' => ['fe87', 'fe88', 'fe87', 'fe88'],
        'ئ' => ['fe89', 'fe8a', 'fe8b', 'fe8c'],
        'ا' => ['fe8d', 'fe8e', 'fe8d', 'fe8e'], 
        'ب' => ['fe8f', 'fe90', 'fe91', 'fe92'],
        'ة' => ['fe93', 'fe94', 'fe93', 'fe94'], 
        'ت' => ['fe95', 'fe96', 'fe97', 'fe98'],
        'ث' => ['fe99', 'fe9a', 'fe9b', 'fe9c'],
        'ج' => ['fe9d', 'fe9e', 'fe9f', 'fea0'],
        'ح' => ['fea1', 'fea2', 'fea3', 'fea4'],
        'خ' => ['fea5', 'fea6', 'fea7', 'fea8'],
        'د' => ['fea9', 'feaa', 'fea9', 'feaa'], 
        'ذ' => ['feab', 'feac', 'feab', 'feac'], 
        'ر' => ['fead', 'feae', 'fead', 'feae'], 
        'ز' => ['feaf', 'feb0', 'feaf', 'feb0'], 
        'س' => ['feb1', 'feb2', 'feb3', 'feb4'],
        'ش' => ['feb5', 'feb6', 'feb7', 'feb8'],
        'ص' => ['feb9', 'feba', 'febb', 'febc'],
        'ض' => ['febd', 'febe', 'febf', 'fec0'],
        'ط' => ['fec1', 'fec2', 'fec3', 'fec4'],
        'ظ' => ['fec5', 'fec6', 'fec7', 'fec8'],
        'ع' => ['fec9', 'feca', 'fecb', 'fecc'],
        'غ' => ['fecd', 'fece', 'fecf', 'fed0'],
        'ف' => ['fed1', 'fed2', 'fed3', 'fed4'],
        'ق' => ['fed5', 'fed6', 'fed7', 'fed8'],
        'ك' => ['fed9', 'feda', 'fedb', 'fedc'],
        'ل' => ['fedd', 'fede', 'fedf', 'fee0'],
        'م' => ['fee1', 'fee2', 'fee3', 'fee4'],
        'ن' => ['fee5', 'fee6', 'fee7', 'fee8'],
        'ه' => ['fee9', 'feea', 'feeb', 'feec'],
        'و' => ['feed', 'feee', 'feed', 'feee'], 
        'ى' => ['feef', 'fef0', 'feef', 'fef0'], 
        'ي' => ['fef1', 'fef2', 'fef3', 'fef4'],
        'لآ' => ['fef5', 'fef6', 'fef5', 'fef6'],
        'لأ' => ['fef7', 'fef8', 'fef7', 'fef8'],
        'لإ' => ['fef9', 'fefa', 'fef9', 'fefa'],
        'لا' => ['fefb', 'fefc', 'fefb', 'fefc'],
    ];

    // Characters that do NOT connect to the next letter (Left-side disconnected)
    // They only have Isolated and Final forms (no Initial or Medial).
    // Effectively, they force the NEXT character to be Initial or Isolated.
    private static $unconnectable = ['ء', 'آ', 'أ', 'ؤ', 'إ', 'ا', 'د', 'ذ', 'ر', 'ز', 'و', 'ى', 'لآ', 'لأ', 'لإ', 'لا'];

    public static function shape(string $text)
    {
        if (!preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return $text;
        }

        $lines = explode("\n", $text);
        $output = [];

        foreach ($lines as $line) {
            $words = explode(' ', $line);
            $newWords = [];
            foreach ($words as $word) {
                if (preg_match('/[\x{0600}-\x{06FF}]/u', $word)) {
                    $newWords[] = self::shapeWord($word);
                } else {
                    $newWords[] = $word;
                }
            }
            // Reverse words for RTL visual rendering in LTR contexts
            $output[] = implode(' ', array_reverse($newWords));
        }

        return implode("\n", $output);
    }

    private static function shapeWord($word)
    {
        // Split into characters (multibyte)
        preg_match_all('/./u', $word, $matches);
        $chars = $matches[0];
        $count = count($chars);
        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $current = $chars[$i];
            
            // If not in our glyph map, keep as is
            if (!isset(self::$glyphs[$current])) {
                $result[] = $current;
                continue;
            }

            $prev = ($i > 0) ? $chars[$i - 1] : null;
            $next = ($i < $count - 1) ? $chars[$i + 1] : null;

            // Determine connectivity
            // Can we connect to previous?
            // Yes if: Not first char AND Previous char is NOT unconnectable
            $connectPrev = ($prev && isset(self::$glyphs[$prev]) && !in_array($prev, self::$unconnectable));
            
            // Can we connect to next?
            // Yes if: Not last char AND Current char is NOT unconnectable
            $connectNext = ($next && isset(self::$glyphs[$next]) && !in_array($current, self::$unconnectable));

            // Select Form Index:
            // 0: Isolated (No Prev, No Next)
            // 1: Initial  (No Prev, Yes Next)
            // 2: Medial   (Yes Prev, Yes Next)
            // 3: Final    (Yes Prev, No Next)
            
            if (!$connectPrev && !$connectNext) {
                $form = 0; // Isolated
            } elseif (!$connectPrev && $connectNext) {
                $form = 1; // Initial
            } elseif ($connectPrev && $connectNext) {
                $form = 2; // Medial
            } else { // $connectPrev && !$connectNext
                $form = 3; // Final
            }

            // Lam-Alef Ligature Handling (Basic)
            // If current is Alef and prev is Lam, we need to skip current and assume prev handled it?
            // Actually, simplified approach: We map Lam-Alef as single special chars in input array if possible
            // But standard typing is separate chars.
            // For this snippet, we stick to char-by-char unless we pre-process ligatures.
            // *Pre-process Ligatures*:
            // We should ideally replace 'ل' + 'ا' with 'لا' before this loop.
            
            $hex = self::$glyphs[$current][$form];
            
            // Convert Hex to Unicode Char
            $result[] = html_entity_decode('&#x'.$hex.';', ENT_COMPAT, 'UTF-8');
        }

        // Reverse characters for RTL visual rendering
        return implode('', array_reverse($result));
    }
}
