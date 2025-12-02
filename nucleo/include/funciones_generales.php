<?php

function convertirNumeroALetras($numero) {
    $formatter = new NumberFormatter("es", NumberFormatter::SPELLOUT);
    $letras = $formatter->format($numero);
    return strtoupper($letras);
}

?>
