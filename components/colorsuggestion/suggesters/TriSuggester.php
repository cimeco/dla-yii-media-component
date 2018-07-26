<?php

namespace quoma\media\components\colorsuggestion\suggesters;

/**
 * Description of ComplementSuggester
 *
 * @author mmoyano
 */
class TriSuggester implements \common\modules\media\components\colorsuggestion\Suggester{
    
    public function getColors($qty, $base, $full, $bg = null) {
        if(count($base) > $qty){
            $base = array_slice($base, 0, $qty);
        }

        $suggestion = [];
        
        foreach($base as $color){
            $suggestion[] = $color->getShift(120)->improveSaturation($color);
            $suggestion[] = $color->getShift(240)->improveSaturation($color);
        }
        
        return array_slice($suggestion, 0, $qty);
    }
    
    public function getName() {
        return 'Tri';
    }
    
    public function applies($qty, $base, $full, $bg = null)
    {
        
        return true;
        
    }
}
