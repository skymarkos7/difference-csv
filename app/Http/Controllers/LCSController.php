<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LCSController extends Controller
{

    /**
     * LCS (Longest Common Subsequence)
     *
     * A longest common substring is a sequence of characters that appears in exactly the same order in both strings,
     * but not necessarily contiguously
     */
    public function longestCommonSubsequence($str1, $str2)
    {
        $len1 = strlen($str1); // lenght string one
        $len2 = strlen($str2); // lenght string two

        $L = []; // Lenght in matrix

        // Inicializa a matriz LCS
        for ($i = 0; $i <= $len1; $i++) {
            for ($j = 0; $j <= $len2; $j++) {
                if ($i == 0 || $j == 0)
                    $L[$i][$j] = 0;
                elseif ($str1[$i - 1] == $str2[$j - 1])
                    $L[$i][$j] = $L[$i - 1][$j - 1] + 1;
                else
                    $L[$i][$j] = max($L[$i - 1][$j], $L[$i][$j - 1]); // insert in L the bigest value
            }
        }

        // Builds the LCS from the LCS matrix
        $index = $L[$len1][$len2];
        $lcs = "";
        $i = $len1;
        $j = $len2;
        while ($i > 0 && $j > 0) { // Enquanto houver elementos
            if ($str1[$i - 1] == $str2[$j - 1]) {
                $lcs = $str1[$i - 1] . $lcs;
                $i--;
                $j--;
                $index--;
            } elseif ($L[$i - 1][$j] > $L[$i][$j - 1])
                $i--;
            else
                $j--;
        }
        return $lcs;
    }

    public function compareSubsequence(Request $request)
    {
        $str1 = $request->str1;
        $str2 = $request->str2;

        echo "String1: $str1 <br> String2: $str2 <br><br>";

        echo "A Subsequence comum mais longa é: <b>" . $this->longestCommonSubsequence($str1, $str2) . "</b><br><br>";

        echo "
            <b> Explain: </b> LCS (Longest Common Subsequence):
            Uma subsequência comum mais longa é uma sequência de caracteres que aparece em exatamente a mesma ordem em ambas as strings,
            mas não necessariamente de forma contígua.<br>
        ";
    }

    /**
     * LCS (Longest Common Subsequence)
     *
     * A longer common substring is a sequence of consecutive
     * characters that appears in both the strings.
     */
    public function longestCommonSubstring($str1, $str2)
    {
        $len1 = strlen($str1);
        $len2 = strlen($str2);

        // Initializes array to store common substring lengths
        $L = [];
        $maxLength = 0; //  Stores the maximum length of the common substring
        $endIndex = 0; // Stores the end index of the common substring

        // Fill the LCS array and find the maximum length of the common substring
        for ($i = 0; $i < $len1; $i++) {
            for ($j = 0; $j < $len2; $j++) {
                if ($str1[$i] == $str2[$j]) {
                    if ($i == 0 || $j == 0)
                        $L[$i][$j] = 1;
                    else
                        $L[$i][$j] = $L[$i - 1][$j - 1] + 1;

                    if ($L[$i][$j] > $maxLength) {
                        $maxLength = $L[$i][$j];
                        $endIndex = $i; // Updates the end index of the common substring
                    }
                } else {
                    $L[$i][$j] = 0; // Don't have commom substring
                }
            }
        }

        // Retorn the commom substring biggest
        return substr($str1, $endIndex - $maxLength + 1, $maxLength);
    }

    public function compareSubstring(Request $request)
    {
        $str1 = $request->str1;
        $str2 = $request->str2;

        echo "String1: $str1 <br> String2: $str2 <br><br>";

        echo "A substring comum mais longa é: <b>" . $this->longestCommonSubstring($str1, $str2) . "</b><br><br>";

        echo "
            <b> Explain:</b> LCS (Longest Common Substring):
            Uma substring comum mais longa é uma sequência
            de caracteres consecutivos que aparece em ambas
            as strings.
        ";
    }
}
