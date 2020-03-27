<?php
function bintohex($bin)
{
    if (ctype_xdigit($bin)) {
        return $bin;
    }
    return bin2hex($bin);
}
