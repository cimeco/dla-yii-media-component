<?php

namespace quoma\media\components\helpers;

use Yii;

/**
 * Description of ColorHelper
 *
 * @author martin
 */
class Color extends \yii\base\Component{
    
    public $r;
    public $g;
    public $b;
    public $alpha;
    
    public function getBWContrast()
    {
        $lum = $this->getLum();
        return $lum > 0.5 ? 'black' : 'white';
    }
    
    public function getLum()
    {
        return ($this->r*0.351 + $this->g*0.587 + $this->b*0.114) / 256;
    }
    
    public function __toString() {
        if($this->alpha !== null){
            return "$this->r,$this->g,$this->b,$this->alpha";
        }else{
            return "$this->r,$this->g,$this->b";
        }
    }
    
    function getHSV()    // RGB values:    0-255, 0-255, 0-255
    {                                // HSV values:    0-360, 0-100, 0-100
        // Convert the RGB byte-values to percentages
        $r = ($this->r / 255);
        $g = ($this->g / 255);
        $b = ($this->b / 255);

        // Calculate a few basic values, the maximum value of R,G,B, the
        //   minimum value, and the difference of the two (chroma).
        $maxRGB = max($r, $g, $b);
        $minRGB = min($r, $g, $b);
        $chroma = $maxRGB - $minRGB;

        // Value (also called Brightness) is the easiest component to calculate,
        //   and is simply the highest value among the R,G,B components.
        // We multiply by 100 to turn the decimal into a readable percent value.
        $computedV = 100 * $maxRGB;

        // Special case if hueless (equal parts RGB make black, white, or grays)
        // Note that Hue is technically undefined when chroma is zero, as
        //   attempting to calculate it would cause division by zero (see
        //   below), so most applications simply substitute a Hue of zero.
        // Saturation will always be zero in this case, see below for details.
        if ($chroma == 0){
            return [
                'h' => 0, 
                's' => 0, 
                'v' => $computedV
            ];
        }

        // Saturation is also simple to compute, and is simply the chroma
        //   over the Value (or Brightness)
        // Again, multiplied by 100 to get a percentage.
        $computedS = 100 * ($chroma / $maxRGB);

        // Calculate Hue component
        // Hue is calculated on the "chromacity plane", which is represented
        //   as a 2D hexagon, divided into six 60-degree sectors. We calculate
        //   the bisecting angle as a value 0 <= x < 6, that represents which
        //   portion of which sector the line falls on.
        if ($r == $minRGB)
            $h = 3 - (($g - $b) / $chroma);
        elseif ($b == $minRGB)
            $h = 1 - (($r - $g) / $chroma);
        else // $g == $minRGB
            $h = 5 - (($b - $r) / $chroma);

        // After we have the sector position, we multiply it by the size of
        //   each sector's arc (60 degrees) to obtain the angle in degrees.
        $computedH = 60 * $h;

        return [
            'h' => $computedH, 
            's' => $computedS, 
            'v' => $computedV
        ];
    }
    
    function hsv2rgb($iH, $iS, $iV) {
        
        if($iH < 0)   $iH = 0;   // Hue:
        if($iH > 360) $iH = 360; //   0-360
        if($iS < 0)   $iS = 0;   // Saturation:
        if($iS > 100) $iS = 100; //   0-100
        if($iV < 0)   $iV = 0;   // Lightness:
        if($iV > 100) $iV = 100; //   0-100
        
        $dS = $iS/100.0; // Saturation: 0.0-1.0
        $dV = $iV/100.0; // Lightness:  0.0-1.0
        $dC = $dV*$dS;   // Chroma:     0.0-1.0
        $dH = $iH/60.0;  // H-Prime:    0.0-6.0
        $dT = $dH;       // Temp variable
        
        while($dT >= 2.0) $dT -= 2.0; // php modulus does not work with float
        
        $dX = $dC*(1-abs($dT-1));     // as used in the Wikipedia link
        
        switch(floor($dH)) {
            case 0:
                $dR = $dC; $dG = $dX; $dB = 0.0; break;
            case 1:
                $dR = $dX; $dG = $dC; $dB = 0.0; break;
            case 2:
                $dR = 0.0; $dG = $dC; $dB = $dX; break;
            case 3:
                $dR = 0.0; $dG = $dX; $dB = $dC; break;
            case 4:
                $dR = $dX; $dG = 0.0; $dB = $dC; break;
            case 5:
                $dR = $dC; $dG = 0.0; $dB = $dX; break;
            default:
                $dR = 0.0; $dG = 0.0; $dB = 0.0; break;
        }
        
        $dM  = $dV - $dC;
        $dR += $dM; $dG += $dM; $dB += $dM;
        $dR *= 255; $dG *= 255; $dB *= 255;
        
        return [
            'r' => round($dR),
            'g' => round($dG),
            'b' => round($dB)
        ];
    }
    
    public function getShift($value = 180)
    {
        // complement
        $hsv = $this->getHSV();
        $hsv['h'] = $this->hueShift($hsv['h'], $value);
        
        $rgb = $this->hsv2rgb($hsv['h'], $hsv['s'],$hsv['v']);
        
        return new self($rgb);
    }
    
    //Adding hueShift 
    public function hueShift($h,$s) { 
        $hue = $h + $s; 
        while ($hue>=360.0) 
            $hue-=360.0; 
        while ($hue<0.0) 
            $hue+=360.0; 
        return $hue; 
    }
    
    public function darken($factor=1)
    {
        
        $lum = $this->getLum();
        
        $ncolor = new \Imagine\Image\Color([$this->r, $this->g, $this->b]);
        $ncolor = $ncolor->darken($lum*255*$factor);
        
        return new self([
            'r' => (int)$ncolor->getRed(),
            'g' => (int)$ncolor->getGreen(),
            'b' => (int)$ncolor->getBlue()
        ]);
        
    }
    
    public function lighten($factor = 0.5)
    {
        
        $lum = $this->getLum();
        
        $ncolor = new \Imagine\Image\Color([$this->r, $this->g, $this->b]);
        $ncolor = $ncolor->lighten(50*$factor);
        
        return new self([
            'r' => (int)$ncolor->getRed(),
            'g' => (int)$ncolor->getGreen(),
            'b' => (int)$ncolor->getBlue()
        ]);
        
    }
    
    /**
     * Mejora la saturacion en basde al color $base. Si el color base y el color
     * actual tienen la misma saturacion, utiliza $factor para reducirlo.
     * @param type $base
     * @param type $or
     * @return \self
     */
    public function improveSaturation($base, $factor =  0.85)
    {
        
        $hsv = $this->getHSV();
        $baseHsv = $base->getHSV();
        
        $baseS = $baseHsv['s'];
        $s = $hsv['s'];
        
        if($baseS < $s){
            $newS = $s - (($s - $baseS) / 2);
        }elseif($baseS > $s){
            $newS = $baseS - (($baseS - $s) / 2);
        }elseif($s < 0.5){
            $newS = $s * (1/$factor);
        }else{
            $newS = $s * $factor;
        }
        
        $rgb = $this->hsv2rgb($hsv['h'], $newS, $hsv['v']);
        
        return new self($rgb);
        
    }
    
    /**
     * 
     * @param type $tolerance
     * @return boolean
     */
    public function getIsWhite($tolerance = 0.05)
    {
        $transparent = 100;
        if(Yii::$app->params['Image[invertAlpha]']){
            $transparent = 0;
        }
        
        if($this->alpha == $transparent){
            return true;
        }
        
        $tolerated = 255 * $tolerance;
        foreach(['r','g','b'] as $component){
            if(255 - $this->$component > $tolerated){
                return false;
            }
        }
        return true;
    }
}
