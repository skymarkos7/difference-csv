<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use SplFileObject;
use Illuminate\Support\Facades\Storage;

class DifferenceController extends Controller
{
    /**
     * 1 - diferença entre os dois CSVs.
     * 2 - Dizer quais são as linhas que existem nos dois ficheiros e que são exactamente iguais...
     * 3 - Quais são as linhas que já existiam mas foram atualizadas.
     * 4 - quais são as linhas novas que foram adicionadas.
     */

    public function compareFilesGit(Request $request)
    {
        // Run git diff command using shell_exec and capture output
        $diffOutput = shell_exec('git diff --no-index ../resources/csv/DadosAntigos.csv ../resources/csv/Dados.csv');

        // Check for diff output
        if ($diffOutput != null) {
            echo "
                <title> Comparando com Git </title>
                <link rel='icon' href='https://git-scm.com/images/logos/downloads/Git-Icon-1788C.png' type='image/png'>
                <h2> legend: </h3>
                <p style='color:red'>   🔴 Red = Removed     </p>
                <p style='color:green'> 🟢 Green = added     </p>
                <p style='color:back'>  ⚫ Black = unchanged </p>
                </br>
                <hr>
                ";
            // Split output into lines and apply highlighting function to each line
            $lines = explode("\n", $diffOutput);
            foreach ($lines as $line) {
                if (substr($line, 0, 1) === '-') {
                    echo "<span style='color:red'>$line</span><br>";
                } elseif (substr($line, 0, 1) === '+') {
                    echo "<span style='color:green'>$line</span><br>";
                } else {
                    echo "$line<br>";
                }
            }
        } else {
            // If there are no differences or an error occurs, display a message
            echo "Não foram encontradas diferenças. Ou o arquivo está inválido.";
        }
    }


    public function saveFile(Request $request)
    {
        // Verificar se os arquivos foram enviados
        if ($request->hasFile('data') && $request->hasFile('oldData')) {
            // Obter os arquivos enviados
            $dataFile = $request->file('data');
            $oldDataFile = $request->file('oldData');

            // Definir o caminho absoluto para a pasta resources/csv
            $path = public_path('../resources/csv');

            // Verificar se já existem arquivos com os mesmos nomes e excluí-los
            if (file_exists("$path/Dados.csv")) {
                unlink("$path/Dados.csv");
            }
            if (file_exists("$path/DadosAntigos.csv")) {
                unlink("$path/DadosAntigos.csv");
            }

            // Salvar os novos arquivos na pasta resources/csv
            $dataFile->move($path, 'Dados.csv');
            $oldDataFile->move($path, 'DadosAntigos.csv');

            return "Arquivos salvos com sucesso.";
        } else {
            return "Por favor, envie ambos os arquivos.";
        }
    }





    public function compareFiles(Request $request)
    {
        $this->readFile($request->data, $request->oldData);
    }

    public function readFile($data, $oldData)
    {
        $row = 0;
        if (($handle1 = fopen($data, "r")) !== FALSE && ($handle2 = fopen($oldData, "r")) !== FALSE) {
            while (($rowData = fgetcsv($handle1, 1000, ";")) !== FALSE && ($rowOldData = fgetcsv($handle2, 1000, ";")) !== FALSE) {  //  PHP way
                $qtFieldsData = isset($rowData) ? count($rowData) : 0;
                $qtFieldsOldData = isset($rowData) ? count($rowOldData) : 0;



                if(($qtFieldsData && $qtFieldsOldData) != 0) {
                    if($rowData == $rowOldData) {
                        echo "<p> As linhas <b>" . $row . "</b> são exactamente iguais... <br /></p>\n";
                    } else {
                        echo "<p> As linhas <b>" . $row . "</b> já existiam mas foram atualizadas. </p>";

                        for ($i = 0; $i < $qtFieldsData; $i++) {
                            echo "O campo <b>" . $rowData[$i] . "</b> foi alterado e agora o dado é: <b>" . $rowOldData[$i] . "</b> <br />\n";

                        }
                    }
                } else {
                    if($qtFieldsData > $qtFieldsOldData) {
                        echo "O arquivo Dados possui a linha" . $row . " que o arquivo DadosAntigos não possui: ";

                    } else if ($qtFieldsOldData > $qtFieldsData) {
                        echo "O arquivo DadosAntigos possui a linha" . $row . " que o arquivo Dados não possui: ";
                    }
                }



                // echo "<p> $qtFieldsData fields of Dados in line $row: <br /></p>\n";
                // print_r($data);
                // echo "<p> $qtFieldsData fields of DadosAntigos in line $row: <br /></p>\n";

                  //

                // echo $data[$row] . "<br />\n";

                $row++;
            }
            // echo $qtFieldsData > $qtFieldsOldData
            //     ? " Foram adicionadas " . $rowData-$rowOldData . " novas linhas"
            //     : " Foram removidas " . $rowOldData-$rowData . " linhas existentes";

            fclose($handle1);
            fclose($handle2);
        }
    }

    // public function compareField()
    // {
    //     for ($i = 0; $i < $fieldsData; $i++) {
    //         if($data[$i] == $oldData[$i]) {
    //             echo "o campo " . $data[$i] . " não foi alterado! <br />\n";
    //         } else {
    //             echo "o campo " . $data[$i] . " foi alterado e agora o dado é: " . $oldData[$i] . "<br />\n";
    //         }

    //     }
    // }


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
