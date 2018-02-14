<?php
//require __DIR__ . '/vendor/autoload.php';

//header('Content-type: application/msword');
//header('Content-Disposition: attachment; filename="agenda_' . date('dmYhis') . '.doc"');

$lista = ''; 
$PATH_url = '';

$strDataInicial = $_REQUEST['dataini'];
$strDataInicial = !empty($strDataInicial) ? $strDataInicial : '01/' . date('m/Y');

$strDataFinal = $_REQUEST['datafim'];
$strDataFinal = !empty($strDataFinal) ? $strDataFinal : date('d/m/Y', strtotime('last day of this month'));

$dataInicialArr = explode('/', $strDataInicial);
$dia = intval($dataInicialArr[0]);
$mes = intval($dataInicialArr[1]);
$ano = intval($dataInicialArr[2]);

$dataFinalArr = explode('/', $strDataFinal);
$diaFinal = intval($dataFinalArr[0]);
$mesFinal = intval($dataFinalArr[1]);
$anoFinal = intval($dataFinalArr[2]);

$title = "AGENDA DE COMPROMISSOS";
$conteudoCelulas = processarLista($lista, $PATH_url); // processar o conteúdo das células
// início html
?>
<html>
    <head>
        <title><?= $title ?></title>
        <?= getStyle() ?>
    </head>
    <body>
        <?= getCabecalho($title, $strDataInicial, $strDataFinal); ?>
        <?php
        $meses = contaMeses($strDataInicial, $strDataFinal); // conta a quantidade de meses do período
        while ($meses > 0) : // para cada mês ?>
            <?= getCalendario($conteudoCelulas, $mes, $dia, $ano, $mesFinal, $diaFinal, $anoFinal); // monta o calendário ?>
            <?= str_repeat('<br>', 3); // linhas em branco após ?>
            <br style='page-break-before: always'><!-- // quebra de página no word -->
            <?php
            $mes++;
            $dia = 1;
            $ano = ($mes > 12) ? ($ano + 1) : $ano;
            $mes = ($mes > 12) ? 1 : $mes;
            $meses--;
        endwhile; ?>
    </body>
</html>






<?php
// início funções

function getStrDiaDataDB($diaHora)
{
    $dh = explode(" ", $diaHora);
    return $dh[0];
}

function getHora($diaHora)
{
    $dh = explode(" ", $diaHora);
    return $dh[1];
}

function getStrHoraDataDB($diaHora)
{
    $explodeHora = explode(":", getHora($diaHora));
    return ($explodeHora[0] . ":" . $explodeHora[1]);
}

function getNumeroDeDiasDoMes($dateTimestamp)
{
    $mes = date('m', $dateTimestamp);
    $ano = date("Y", $dateTimestamp);
    return date("t", mktime(0, 0, 0, $mes, '01', $ano));
}

function getColuna($dateTimestamp, $matrizDias)
{
    $date = date('d/m', $dateTimestamp);
    $conteudo = (!empty($matrizDias[$date]) ? $matrizDias[$date] : '');
    $espacamento = str_repeat('<br>', 10);
    $html = "
        <td valign='top'>
            <span class='tit_dia'>{$date}</span>
            <div class='compromisso'>
                {$conteudo}
                {$espacamento}
            </div>
        </td>";
    return $html;
}


function getLinhaTitulos()
{
    $class = 'titulo_cabecalho';
    $arrDias = array('SEG' => 15, 'TER' => 15, 'QUA' => 15, 'QUI' => 15, 'SEX' => 15, 'SAB' => 13, 'DOM' => 10);
    $linhaTitulosTabela = "<tr>";
    foreach ($arrDias as $dia => $width) {
        $linhaTitulosTabela .= "<td class='{$class}' width='{$width}'>{$dia}</td>";
    }
    $linhaTitulosTabela .= "</tr>";
    return $linhaTitulosTabela;
}

function getCabecalho($title, $strDataInicial, $strDataFinal)
{
    $tb = "<table style='width: 100%'>
              <tr><td class='titulo' align='center'>{$title}</td><td rowspan='2'></td></tr>
              <tr><td class='titulo2'>{$strDataInicial} - {$strDataFinal}</td></tr>
          </table>";
    return $tb;
}

function getStyle()
{
    $style = "
        <style type='text/css'>
            body { width: 100%; mso-padding-alt:0cm 0.4pt 0cm 0.4pt; margin:0 }
            @page { margin: 0cm }
            table.bordasimples { width: 99% }
            table.bordasimples { border-collapse: collapse; }
            table.bordasimples tr td { border: 1px solid #CCCCCC; }
            table.bordasimples tr { font-size: 7pt; font-family: Arial Black }
            .titulo_data { background-color: #D1D1D1; font-size: 7pt; font-family: Arial Black; text-align: center; }
            .titulo_cabecalho { background-color: #D1D1D1; font-size: 7pt; font-family: Arial; text-align: center; }
            .tit_dia { background-color: #D1D1D1; font-size: 8pt; font-family: Arial; text-align: center; width: 100% }
            .compromisso { vertical-align: top; font-size: 7pt; font-family: Arial; min-height: 68px; }
            .titulo { font-family: Arial Black; font-size: 12pt; }
            .titulo2 { font-family: Arial; font-size: 8pt; text-align: center; }
            .convite { font-family: Verdana; font-size: 7pt; font-weight: Bold; background: #FFFFBB; color: #0000FF; 
                        border: 1px solid #000; }
            .conteudo { font-family: Arial; font-size: 7pt; color: #000000; }
            .confirmada { border: 2px solid #05ad28; padding: 2px; }
            .naoconfirmada { border: 2px solid #FF0000; padding: 2px; }
        </style>";
    return $style;
}

function getColunasAntecedentes($dia, $mes, $ano, $diaDaSemana, $matrizDias)
{
    $html = '';
    $qtdAnteceder = ($diaDaSemana - 1); // qtd de dias a acrescentar antes
    $qtdAnteceder = $qtdAnteceder < 0 ? 6 : $qtdAnteceder;
    $anteDia = ($dia - $qtdAnteceder);
    $i = 0;
    while ($i < $qtdAnteceder) {
        $anteTimestamp = mktime(0, 0, 0, $mes, $anteDia, $ano);
        $html .= getColuna($anteTimestamp, $matrizDias);
        $i++;
        $anteDia++;
    }
    return $html;
}

function getColunasPosteriores($dia, $mes, $ano, $matrizDias)
{
    $html = '';
    $ano = ($mes > 12) ? ($ano + 1) : $ano;
    $mes = ($mes > 12) ? 1 : $mes;
    $dateTimestamp = mktime(0, 0, 0, $mes, $dia, $ano);
    $diaDaSemana = date('w', $dateTimestamp);
    $qtdPosterior = (8 - $diaDaSemana); // qtd de dias a acrescentar depois pra completar o calendario
    $qtdPosterior = ($qtdPosterior > 7) ? ($qtdPosterior - 7) : $qtdPosterior;
    $qtdPosterior = ($qtdPosterior == 8) ? 0 : $qtdPosterior;
    //echo "dia $dia - mes $mes - ano $ano - dia semana $diaDaSemana - qtd $qtdPosterior";
    $i = 1;
    while ($i <= $qtdPosterior) {
        $anteTimestamp = mktime(0, 0, 0, $mes, $dia, $ano);
        $html .= getColuna($anteTimestamp, $matrizDias);
        $dia++;
        $i++;
    }
    return $html;
}

function contaMeses($strDataInicial, $strDataFinal)
{
    $dataInicialArr = explode('/', $strDataInicial);
    $mes = intval($dataInicialArr[1]);
    $ano = intval($dataInicialArr[2]);

    $dataFinalArr = explode('/', $strDataFinal);
    $mesFinal = intval($dataFinalArr[1]);
    $anoFinal = intval($dataFinalArr[2]);

    $a = ($anoFinal - $ano);
    $m1 = (12 - $mes);
    $m2 = $mesFinal;
    $result = ($m1 + $m2 + 1);
    if ($a == 0) {
        $result = $mesFinal - $mes + 1;
    } else if (($a - 1) > 0) {
        $m1 = (12 - $mes);
        $m2 = $mesFinal;
        $result = (($a - 1) * 12) + ($m1 + $m2) + 1;
    }
    return $result;
}

function processarLista($lista, $pathImg)
{
    $conteudoCelulas = array();
    if (!empty($lista)) {
        foreach ($lista as $key => $reg) {
            $txtDate = getStrDiaDataDB($reg['inicio']);
            $txtHora = getStrHoraDataDB($reg['inicio']);
            $conteudoCelulas[$txtDate] = getConteudoCelula($txtHora, $reg, $pathImg);
        }
    }
}

function getConteudoCelula($txtHora, $reg, $pathImg)
{
    $nome = str_replace(" ", "&nbsp;", $reg['a903_nome']);
    $local = ((!empty($reg['local'])) ? "<br><b>Local: {$reg['local']}</b>" : "");
    $imgClock = "<img src='{$pathImg}imagens/famfamfam/clock.png'>";
    $imgUser = "<img src='{$pathImg}imagens/famfamfam/user_gray.png'>";
    $style = "text-decoration:underline;font-weight:bold";
    $html = "<div class='compromisso'>
                {$imgClock}
                <span style='{$style}'> {$txtHora} </span>&nbsp;
                {$imgUser}
                <span style='{$style}'> {$nome} </span>
                <br>
                <b>{$reg['tipo_compromisso']}</b><br>
                <b>{$reg['nome_status']}</b><br>
                <i>{$reg['evento']}</i>
                {$local}
                <br><br>
            </div>";
    return $html;
}

function getCalendario($conteudoCelulas, $mes, $dia, $ano, $mesFinal, $diaFinal, $anoFinal)
{
    $html = '';
    $html .= "<table class='bordasimples' align='center'>";
    $html .= getLinhaTitulos(); // incio da tabela e linha de titulos dos dias da semana
    $dateTimestamp = mktime(0, 0, 0, $mes, $dia, $ano);
    $diaDaSemana = date('w', $dateTimestamp);
    $numDiaNoMes = getNumeroDeDiasDoMes($dateTimestamp);
    $qtd = (($dia != 1) ? ($numDiaNoMes - $dia + 1) : $numDiaNoMes);

    // caso o primeiro dia esteja no meio da semana, preencher dias antes da data inicial a partir de segunda feira
    $html .= getColunasAntecedentes($dia, $mes, $ano, $diaDaSemana, $conteudoCelulas);

    for ($i = 0; $i < $qtd; $i++) {
        $html .= getColuna($dateTimestamp, $conteudoCelulas);
        $html .= (($diaDaSemana == 0) ? "</tr>" : ""); // chegou domingo quebra linha
        $dia++; // vai para o próximo dia
        if($mes == $mesFinal && $dia == $diaFinal && $ano == $anoFinal) { // se for ultimo dia especificado na data final
            break; // quebrar o loop
        }
        $dateTimestamp = mktime(0, 0, 0, $mes, $dia, $ano);
        $diaDaSemana = date('w', $dateTimestamp);
    }
    if($mes != $mesFinal && $dia != $diaFinal && $ano != $anoFinal) {
        $dia = ($dia > $numDiaNoMes ? 1 : $dia);
        $mes++;
    }
    $html .= getColunasPosteriores($dia, $mes, $ano, $conteudoCelulas); // caso o ultimo dia não caia no ultimo domingo, preencher dias depois

    $html .= "</tr>";
    $html .= "</table>";
    return $html;
}
