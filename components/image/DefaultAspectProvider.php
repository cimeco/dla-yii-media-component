<?php

namespace quoma\media\components\image;

/**
 * Description of DefaultAspectProvider
 *
 * @author martin
 */
class DefaultAspectProvider implements AspectProviderInterface{
    
    
    public function getAspects() {
        $steps = [
            ['name' => 'Mobile','aspect' => 1, 'minWidth' => 300, 'minHeight' => 300],
            ['name' => '16/9','aspect' => 16/9, 'minWidth' => 800, 'minHeight' => 450],
            //['name' => '4/3','aspect' => 4/3, 'minWidth' => 600, 'minHeight' => 450],
        ];
        
        return $steps;
    }

}
