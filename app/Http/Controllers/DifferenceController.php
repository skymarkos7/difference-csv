<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use SplFileObject;

class DifferenceController extends Controller
{

    public function compareFiles(Request $request)
    {
        $this->readFile($request->Dados);
        $this->readFile($request->DadosAntigos);
    }

    public function readFile($file)
    {
        $row = 1;
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {  //  PHP way
                $fields = count($data);
                echo "<p> $fields fields in line $row: <br /></p>\n";
                $row++;

                for ($i = 0; $i < $fields; $i++) {
                    echo $data[$i] . "<br />\n";
                }
            }
            fclose($handle);
        }
    }


    /**
     * FIRST WAY
     * Adapted from Stack Over Flow
     */
    public function compareFilesSof(Request $request)
    {
        //---- init
        $strFileName1 = isset($request['Dados']) ? $request['Dados'] : '';
        $strFileName2 = isset($request['DadosAntigos']) ? $request['DadosAntigos'] : '';

        if (!$strFileName1) {
            die("I need the first file (Dados)");
        }
        if (!$strFileName2) {
            die("I need the second file (DadosAntigos)");
        }

        try {
            $arrFile1 = $this->parseDataCsv($request['Dados']);
            $arrFile2 = $this->parseDataCsv($request['DadosAntigos']);
        } catch (Exception $e) {
            die($e->getMessage());
        }

        $rowCount1 = count($arrFile1);
        $rowCount2 = count($arrFile2);

        $colCount1 = count($arrFile1[0]);
        $colCount2 = count($arrFile2[0]);

        $highestRowCount = $rowCount1 > $rowCount2 ? $rowCount1 : $rowCount2;
        $highestColCount = $colCount1 > $colCount2 ? $colCount1 : $colCount2;

        $row = 0;
        $err = 0;

        //---- code

        echo "<h2>comparing $strFileName1 and $strFileName2</h2>";
        echo "\n<table border=1>";
        echo "\n<tr><th>Err<th>Row#<th>Col#<th>Data in $strFileName1<th>Data in $strFileName2";
        while ($row < $highestRowCount) {
            if (!isset($arrFile1[$row])) {
                echo "\n<tr><td>Row missing in $strFileName1<th>$row";
                $err++;
            } elseif (!isset($arrFile1[$row])) {
                echo "\n<tr><td>Row missing in $strFileName2<th>$row";
                $err++;
            } else {
                $col = 0;
                while ($col < $highestColCount) {
                    if (!isset($arrFile1[$row][$col])) {
                        echo "\n<tr><td>Data missing in $strFileName1<td>$row<td>$col<td><td>" . htmlentities($arrFile2[$row][$col]);
                        $err++;
                    } elseif (!isset($arrFile2[$row][$col])) {
                        echo "\n<tr><td>Data missing in $strFileName1<td>$row<td>$col<td>" . htmlentities($arrFile1[$row][$col]) . "<td>";
                        $err++;
                    } elseif ($arrFile1[$row][$col] != $arrFile2[$row][$col]) {
                        echo "\n<tr><td>Data mismatch";
                        echo "<td>$row <td>$col";
                        echo "<td>" . htmlentities($arrFile1[$row][$col]);
                        echo "<td>" . htmlentities($arrFile2[$row][$col]);
                        $err++;
                    }
                    $col++;
                }
            }
            $row++;
        }
        echo "</table>";

        if (!$err) {
            echo "<br/>\n<br/>\nThe two csv data files seem identical<br/>\n";
        } else {
            echo "<br/>\n<br/>\nThere are $err differences";
        }
    }

    public function parseDataCsv($strFilename)
    {
        $arrParsed = array();
        $handle = fopen($strFilename, "r");
        if ($handle) {
            while (!feof($handle)) {
                $data = fgetcsv($handle, 0, ',', '"');
                if (empty($data)) continue; //empty row
                $arrParsed[] = $data;
            }
            fclose($handle);
        } else {
            throw new Exception("File read error at $strFilename");
        }
        return $arrParsed;
    }
}
