<?php

namespace quoma\media\components\colorsuggestion\suggesters;

/**
 * Description of ComplementSuggester
 *
 * @author mmoyano
 */
class DirectSuggester implements \common\modules\media\components\colorsuggestion\Suggester{
    
    public function getColors($qty, $base, $full, $bg = null) 
    {
        
        if($bg && !$bg->isWhite){
            array_unshift($base, $bg);
        }
        
        if(count($base) >= $qty){
            return array_slice($base, 0, $qty);
        }

        $suggestion = [];
        
        foreach($base as $color){
            $suggestion[] = clone $color;
        }
        
        if(count($suggestion) < $qty && !$this->isThere($base, 'black')){
            $suggestion[] = new \common\modules\media\components\helpers\Color([
                'r' => 35, 'g' => 35, 'b' => 35
            ]);
        }
        
        if(count($suggestion) < $qty && !$this->isThere($base, 'white')){
            $suggestion[] = new \common\modules\media\components\helpers\Color([
                'r' => 255, 'g' => 255, 'b' => 255
            ]);
        }
        
        return array_slice($suggestion, 0, $qty);
    }
    
    public function getName() 
    {
        return 'Direct';
    }
    
    public function applies($qty, $base, $full, $bg = null)
    {
        return true;
    }
    
    private function isThere($base, $color = 'white')
    {
        
        if($color == 'white'){
            $cmp = function($color){
                if($color >= 245){
                    return true;
                }
                return false;
            };
        }else{
            $cmp = function($color){
                if($color <= 35 ){
                    return true;
                }
                return false;
            };
        }
        
        foreach($base as $c){
            if($cmp($c->r) && $cmp($c->g) && $cmp($c->b)){
                return true;
            }
        }
        
        return false;
        
    }
    
}
