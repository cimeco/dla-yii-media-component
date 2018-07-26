<?php

namespace quoma\media\components\colorsuggestion;

/**
 *
 * @author mmoyano
 */
interface Suggester {
    public function getColors($qty, $base, $full, $bg = null); 
    public function getName();
    public function applies($qty, $base, $full, $bg = null);
}
