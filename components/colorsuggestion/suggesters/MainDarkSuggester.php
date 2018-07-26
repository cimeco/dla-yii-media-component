<?php

namespace quoma\media\components\colorsuggestion\suggesters;

/**
 * Description of ComplementSuggester
 *
 * @author mmoyano
 */
class MainDarkSuggester implements \common\modules\media\components\colorsuggestion\Suggester{
    
    public function getColors($qty, $base, $full, $bg = null) {
        
        $main = array_shift($base);

        $suggestion = [];
        
        for($i=1;$i<=$qty;$i++){
            $suggestion[] = $main->darken(1/($qty/$i))->improveSaturation($main);
        }
        
        return $suggestion;
    }
    
    public function getName() {
        return 'Main Darken';
    }
    
    public function applies($qty, $base, $full, $bg = null)
    {
        
        $main = array_shift($base);
        
        if($main->getLum() < 0.1){
            return false;
        }
        
        return true;
        
    }
    
}
