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

    public function saveFile(Request $request)
    {
        // Verify if files are already sended
        if ($request->hasFile('data') && $request->hasFile('oldData')) {
            // Obter os arquivos enviados
            $dataFile = $request->file('data');
            $oldDataFile = $request->file('oldData');

            // Default path to files
            $path = public_path('../resources/csv');

            // Verify if exist files with the same name and delete then
            if (file_exists("$path/Dados.csv")) {
                unlink("$path/Dados.csv");
            }
            if (file_exists("$path/DadosAntigos.csv")) {
                unlink("$path/DadosAntigos.csv");
            }

            // save new files on folder resources/csv
            $dataFile->move($path, 'Dados.csv');
            $oldDataFile->move($path, 'DadosAntigos.csv');

            return "Arquivos salvos com sucesso.";
        } else {
            return "Por favor, envie ambos os arquivos.";
        }
    }

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

    public function compareFilesSimple(Request $request)
    {
        // Path from file CSV
        $dataFile = '../resources/csv/Dados.csv';
        $oldDataFile = '../resources/csv/DadosAntigos.csv';

        if (!file_exists($dataFile) || !file_exists($oldDataFile))
            return "<h1>Ambos os arquivos precisam ser enviados</h1>";

        $row = 0;
        if (($handle1 = fopen($dataFile, "r")) !== FALSE && ($handle2 = fopen($oldDataFile, "r")) !== FALSE) {
            $response[] = "
            <title> Compare SIMPLE </title>
            <link rel='icon' href='https://upload.wikimedia.org/wikipedia/commons/4/4d/Simple_Shoes_logo.png'>
            ";
            while (($rowData = fgetcsv($handle1, 1000, ";")) !== FALSE && ($rowOldData = fgetcsv($handle2, 1000, ";")) !== FALSE) {
                $qtFieldsData = isset($rowData) ? count($rowData) : 0;
                $qtFieldsOldData = isset($rowOldData) ? count($rowOldData) : 0;

                if ($qtFieldsData != 0 && $qtFieldsOldData != 0) {
                    if ($rowData == $rowOldData) {
                        $response[] = "<p> As linhas <b>" . $row . "</b> são exatamente iguais... <br /></p>\n";
                    } else {
                        $response[] = "<p> As linhas <b>" . $row . "</b> já existiam mas foram atualizadas. </p>";

                        for ($i = 0; $i < $qtFieldsData; $i++) {
                            $response[] = "O campo <b>" . $rowData[$i] . "</b> foi alterado e agora o dado é: <b>" . $rowOldData[$i] . "</b> <br />\n";
                        }
                    }
                } else {
                    if ($qtFieldsData > $qtFieldsOldData) {
                        $response[] = "O arquivo $dataFile possui a linha " . $row . " que o arquivo $oldDataFile não possui: ";
                    } else if ($qtFieldsOldData > $qtFieldsData) {
                        $response[] = "O arquivo $oldDataFile possui a linha " . $row . " que o arquivo $dataFile não possui: ";
                    }
                }

                $row++;
            }

            fclose($handle1);
            fclose($handle2);
        }
        $response = implode($response);
        return $response;
    }



    function LCSLength($str1, $str2)
    {
        $m = strlen($str1);
        $n = strlen($str2);

        $L = [];
        for ($i = 0; $i <= $m; $i++) {
            for ($j = 0; $j <= $n; $j++) {
                if ($i == 0 || $j == 0)
                    $L[$i][$j] = 0;
                elseif ($str1[$i - 1] == $str2[$j - 1])
                    $L[$i][$j] = $L[$i - 1][$j - 1] + 1;
                else
                    $L[$i][$j] = max($L[$i - 1][$j], $L[$i][$j - 1]);
            }
        }

        return $L[$m][$n];
    }

    function compareCSV($file1, $file2)
    {
        $data1 = file($file1);
        $data2 = file($file2);

        $diff = [];

        foreach ($data1 as $line1) {
            $found = false;
            foreach ($data2 as $line2) {
                if (trim($line1) == trim($line2)) {
                    $found = true;
                    if ($line1 !== $line2) {
                        $diff[] = ['status' => 'modified', 'line' => $line1];
                    } else {
                        $diff[] = ['status' => 'unchanged', 'line' => $line1];
                    }
                    break;
                }
            }
            if (!$found) {
                $diff[] = ['status' => 'removed', 'line' => $line1];
            }
        }

        foreach ($data2 as $line2) {
            $found = false;
            foreach ($data1 as $line1) {
                if (trim($line1) == trim($line2)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $diff[] = ['status' => 'added', 'line' => $line2];
            }
        }

        return $diff;
    }

    public function compareFileLcs()
    {
        $file1 = '../resources/csv/Dados.csv';
        $file2 = '../resources/csv bkp/DadosAntigos.csv';

        if (!file_exists($file1) || !file_exists($file2))
            return "<h1>Ambos os arquivos precisam ser enviados</h1>";

        $dataDiff = $this->compareCSV($file1, $file2);
        echo "
        <title> Comparando com LCS </title>
        <link rel='icon' href='https://lcs.com.br/wp-content/uploads/2021/08/cropped-logo.png'>
        ";
        echo "<ul>";
        foreach ($dataDiff as $item) {
            $line = htmlspecialchars($item['line']);
            if ($item['status'] == 'removed') {
                echo "<li><span style='color:red;'><b>Removed:</b> $line</span></li>";
            } elseif ($item['status'] == 'added') {
                echo "<li><span style='color:green;'><b>Added:</b> $line</span></li>";
            } elseif ($item['status'] == 'modified') {
                echo "<li><span style='color:blue;'><b>Modified:</b> $line</span></li>";
            } else {
                echo "<li><span style='color:black; font'><b>Unchanged:</b> $line</span></li>";
            }
        }
        echo "</ul>";
    }

    public function comparethinking()
    {
        // ini_set('auto_detect_line_endings', TRUE); // To mac
        $rows = array_map('str_getcsv', file('../resources/csv/Dados.csv'));
        $header = array_shift($rows);
        $removeTrash = array_shift($rows);
        $fields = [];
        echo "<style>
            table {
                border-collapse: collapse;
            }
            tr {
                border: solid;
                border-left: solid;
                border-width: 1px 0;
            }

        </style>";

        echo "<table>";
        foreach ($rows as $key => $row) {
            $fields[] = explode(';', $row[0]);
            echo "<tr>";
            foreach ($fields[0] as $key => $field) {
                echo "<td> . $field . </td>";
            }
            echo "</tr>";
        }
        echo "</table";
        // return "<pre> . $mountSheet . </pre>";
    }
}
