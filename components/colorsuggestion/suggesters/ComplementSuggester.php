<?php

namespace quoma\media\components\colorsuggestion\suggesters;

/**
 * Description of ComplementSuggester
 *
 * @author mmoyano
 */
class ComplementSuggester implements \common\modules\media\components\colorsuggestion\Suggester{
    
    public function getColors($qty, $base, $full, $bg = null) {
        
        if(count($base) > $qty){
            $base = array_slice($base, 0, $qty);
        }

        $suggestion = [];
        
        foreach($base as $color){
            $suggestion[] = $color->getShift(180);
        }
        
//        if($full && $qty - count($suggestion)){
//            $tri = new TriSuggester();
//            $suggestion = array_merge($suggestion, $tri->getColors($qty, $base, false));
//        }
        
        return array_slice($suggestion, 0, $qty);
    }
    
    public function getName() {
        return 'Complement';
    }
    
    public function applies($qty, $base, $full, $bg = null){
        return true;
    }
    
}
