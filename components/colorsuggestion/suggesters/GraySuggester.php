<?php

namespace quoma\media\components\colorsuggestion\suggesters;

/**
 * Description of ComplementSuggester
 *
 * @author mmoyano
 */
class GraySuggester implements \common\modules\media\components\colorsuggestion\Suggester{
    
    public function getColors($qty, $base, $full, $bg = null) {
        
        $suggestion = [];
        
        foreach($base as $color){
            $averageLum = $color->getLum();
        }
        $averageLum = $averageLum / count($base);
        
        if($averageLum < 0.2){
            $base = sqrt($averageLum) * 255;
            $step = ((255 - $base)/$qty) * (1-$averageLum);

            for($i = 1; $i <= $qty; $i++){
                $val = (int)($base + $i * $step);
                $suggestion[] = new \common\modules\media\components\helpers\Color(
                    [
                        'r' => $val,
                        'g' => $val,
                        'b' => $val
                    ]
                );
            }
        }else{
            $base = $averageLum*$averageLum*255;
            $step = ((255 - $base)/$qty) * (1-$averageLum);

            for($i = 1; $i <= $qty; $i++){
                $val = (int)($base + $i * $step);
                $suggestion[] = new \common\modules\media\components\helpers\Color(
                    [
                        'r' => $val,
                        'g' => $val,
                        'b' => $val
                    ]
                );
            }
        }
        
        return $suggestion;
    }
    
    public function getName() {
        return 'Gray';
    }
    
    public function applies($qty, $base, $full, $bg = null)
    {
        return true;
    }
    
}
