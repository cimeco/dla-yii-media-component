<?php

namespace quoma\media\components\colorsuggestion\suggesters;
/**
 * Description of ComplementSuggester
 *
 * @author mmoyano
 */
class DarkSuggester implements \common\modules\media\components\colorsuggestion\Suggester{
    
    public function getColors($qty, $base, $full, $bg = null) 
    {
        if(count($base) > $qty){
            $base = array_slice($base, 0, $qty);
        }

        $suggestion = [];
        
        foreach($base as $color){
            $suggestion[] = $color->darken();
        }
        
        return array_slice($suggestion, 0, $qty);
    }
    
    public function getName() 
    {
        return 'Darken';
    }
    
    public function applies($qty, $base, $full, $bg = null)
    {
        $average = 0;
        foreach($base as $c){
            $average += $c->getLum();
        }
        $average = $average/count($base);
        
        if($average < 0.1){
            return false;
        }
        
        return true;
    }
    
}
