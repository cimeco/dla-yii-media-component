<?php

namespace quoma\media\components\colorsuggestion\suggesters;

/**
 * Description of ComplementSuggester
 *
 * @author mmoyano
 */
class MainLightSuggester implements \common\modules\media\components\colorsuggestion\Suggester{
    
    public function getColors($qty, $base, $full, $bg = null) {
        
        $main = array_shift($base);

        $suggestion = [];
        
        for($i=1;$i<=$qty;$i++){
            $suggestion[] = $main->lighten(1/($qty/$i));
        }
        
        return $suggestion;
    }
    
    public function getName() {
        return 'Main Lighten';
    }
    
    public function applies($qty, $base, $full, $bg = null)
    {
        
        $main = array_shift($base);
        
        if($main->getLum() > 0.2){
            return false;
        }
        
        return true;
        
    }
}
