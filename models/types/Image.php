<?php

namespace quoma\media\models\types;

use quoma\modules\config\models\Config;
use Yii;
use quoma\media\models\Type;
use quoma\media\models\Media;
use quoma\media\models\Sized;
use yii\helpers\FileHelper;

/**
 * Description of Image
 *
 * TODO: https://developer.mozilla.org/en-US/docs/Learn/HTML/Multimedia_and_embedding/Responsive_images
 * 
 * @author mmoyano
 */
class Image extends Media{
    
    public $file;
    
    public function init()
    {
        parent::init();
        $this->type = 'Image';
    }
    
    public function rules()
    {
        
        $rules = parent::rules();
        $rules[] = ['file', 'image',
            //TODO: PARAM:
            'extensions' => 'png,jpg,jpeg',
            'minWidth' => Config::getValue('image_min_width'), //$params['minWidth'],
            'maxWidth' => Config::getValue('image_max_width'), //$params['maxWidth'],
            'minHeight' => Config::getValue('image_min_height'), //$params['minHeight'], 
            'maxHeight' => Config::getValue('image_max_height'), //$params['maxHeight'],
        ];
        
        return $rules;
    }

    public static function find()
    {
        return new Type(get_called_class(), ['type' => self::$type]);
    }

    public function render($width = null, $height = null, $options = [])
    {
        if($width === null && $height === null){
            return \yii\helpers\Html::img($this->url, $options);
        }
        
        $mobileOptimized = true;
        if(isset($options['mobileOptimized'])){
            $mobileOptimized = (boolean)$options['mobileOptimized'];
            unset($options['mobileOptimized']);
        }
        
        $content= \yii\helpers\Html::img($this->getSizedUrl($width, $height, $mobileOptimized), $options);

        if (($this->scenario === 'insert-cont' && Config::getValue('insert_image_epigraph')) && !(empty($this->title) && empty($this->description))){
           $epigraph= \yii\bootstrap\Html::tag('div', $this->title . ' / ' . $this->description, ['class' => 'image-caption']);

           $content .= $epigraph;
        }

        return $content;
    }
    
    public function renderPreview()
    {
        return \yii\helpers\Html::img($this->getSizedUrl(300, 300));
    }
    
    protected function process($path)
    {
        
        $size = getimagesize($path);
        $this->width = $size[0];
        $this->height = $size[1];
        
        //Guardamos o no el original?
        if(!Config::getValue('save_original')){
            
            $basePath = $this->getBasePath();

            //Este es el original, que luego se borra
            $sourcePath = $basePath.DIRECTORY_SEPARATOR.$this->relative_url;
            
            //Nueva imagen: Necesitamos un "sized" del tamaño original para evitar que lo genere desde una imagen de menor calidad
            $sized = $this->newSized($this->width, $this->height);
            $this->relative_url = $sized->relative_url;
            
            $this->save();

            //Borramos el original
            unlink($sourcePath);
            
        }
        
    }
    
    /**
     * Devuelve una url del tamaño requerido. Nunca se devuelve la imagen original
     * por no estar comprimida.
     * @param int $width
     * @param  int $height
     * @return string url de la imagen
     * @throws \InvalidArgumentException
     */
    public function getSizedUrl($width, $height, $mobileOptimized = false)
    {
        //Si el archivo original no existe, devolvemos false
        $basePath = $this->getBasePath();
        $path = $basePath.DIRECTORY_SEPARATOR.$this->relative_url;
        
        if(!file_exists($path)){
            return false;
        }
        
        if($mobileOptimized 
                && Yii::$app->has('devicedetect') 
                && Yii::$app->devicedetect->isMobile()){
            $width = isset(Yii::$app->params['mobile_image_width']) ? Yii::$app->params['mobile_image_width'] : 400;
            $height = isset(Yii::$app->params['mobile_image_height']) ? Yii::$app->params['mobile_image_height'] : 400;
        }
        
        //Si es true, se fuerza al relacion de aspecto cuando la imagen es menor al tamaño solicitado
        $forceAspect = Config::getValue('force_aspect');
        
        /**
         * 1:
         *  Si el ancho o el alto de la imagen es menor al requerido, utilizamos el valor actual
         *  De no hacerlo, nunca se encontrara la version del tamanio solicitado
         * 
         * 2: 
         *  La relación de aspecto forzada se da cuando el tamaño solicitado es menor
         * al tamaño real de la imagen. Por ejemplo, tenemos una imagen de 500x350, y 
         * se solicita una imagen de 550 x 300. Si no se fuerza el aspecto, la imagen
         * devuelta será de 500x300, es decir, se mantiene el ancho y se ajusta el alto
         * de acuerdo al alto solicitado, pero la imagen no tiene la relación de aspecto
         * solicitada. Si se fuerza la relación de aspecto, la imagen devuelta será de
         * 500 x 272, es decir, no se devuelve una imagen del tamaño solicitado por 
         * ser imposible (salvo que se rellene), sino que se prioriza el aspecto y se
         * ajusta el valor del alto solicitado de acuerdo al ancho real.
         * 
         *  Si debemos forzar la relación de aspecto, se debe calcular el nuevo ancho
         * o alto, en función del tamaño real de la imagen.
         */
        if($width !== null && $this->sourceWidth < $width){
           
            //Si debemos forzar la relación de aspecto, debemos calcular el alto
            if($forceAspect && $height !== null){
                $height = $this->sourceWidth * $height / $width;
            }
           
            $width = $this->sourceWidth;
           
        }
        
        /**
         * $forceAspect = true:
         * En el caso de una imagen solicitada de menor ancho y menor alto que la imagen
         * original, puede ocurrir que luego del paso anterior, el alto calculado sea
         * superior al alto real de la imagen. En ese caso, el siguiente if será evaluado
         * como true, se calculará nuevamente el ancho en función del alto real de la
         * imagen, y luego se establecerá el alto igual al alto real de la imagen.
         */
        if($height !== null && $this->sourceHeight < $height){
            
            //Si debemos forzar la relación de aspecto, debemos calcular el ancho
            if($forceAspect && $width !== null){
                $width = $this->sourceHeight * $width / $height;
            }
            
            $height = $this->sourceHeight;
        }
        
        //Si solo se provee ancho, calculamos el alto para que mantenga la rel de aspecto
        if($height < 1){
            $height = (int)($this->sourceHeight * $width / $this->sourceWidth);
        }
        //Si solo se provee alto, calculamos el ancho para que mantenga la rel de aspecto
        if($width < 1){
            $width = (int)($this->sourceWidth * $height / $this->sourceHeight);
        }

        //Buscamos si ya ha sido recortada/reducida
        $sized = $this->findSized($width,$height);
        if($sized){
            return $sized->url;
        }
        
        //Si no se provee ni ancho ni alto, error
        if ($height < 1 && $width < 1) {
            throw new \InvalidArgumentException('You should provide width or height.');
        }
        
        $sized = $this->newSized($width, $height);
        return $sized->url;
        
    }
    
    public function findSized($width, $height)
    {
        
        //Buscamos primero del tamaño exacto:
        $sized = $this->getSizeds()->andWhere('height is not null')->andWhere('width is not null')
                ->andWhere(['width' => $width, 'height' => $height])
                ->orderBy(['sized_id' => SORT_DESC])->limit(1)->one();
        if($sized){
            return $sized;
        }
        
        //Sino encontramos del tamaño exacto, buscamos una imagen de mismo aspecto y la reducimos
        
        $aspect = $width/$height;
        
        /**
         * Buscamos una imagen de iguales dimensiones o de igual relación de aspecto (con una mínima tolerancia).
         * Si la imagen tiene la misma relación de aspecto pero no las mismas dimensiones,
         * debemos reducirla.
         */
        
        $aspectTolerance = $width > $height ? (($height+1)/$height)-1 : (($width+1)/$width)-1; //Tolerancia para relación de aspecto
        
        $toleratedWidth = $width-1; //Toleramos una diferencia de 1px
        $toleratedHeight = $height-1;
        
        //Buscamos...
        $sized = $this->getSizeds()->andWhere('height is not null')->andWhere('width is not null')->andWhere(
            "(".(1-$aspectTolerance)."*(width/height)) <= $aspect AND (".(1+$aspectTolerance)."*(width/height)) >= $aspect AND width>=$toleratedWidth AND height>=$toleratedHeight"
        )->orderBy(['sized_id' => SORT_DESC])->limit(1)->one();

        if(!$sized){
            return false;
        }
        
        /**
         * Dado que la relación de aspecto tiene un valor de tolerancia, es posible
         * que el ancho o alto sean 1px mas bajo del esperado.
         */
        if($sized->width <= $width || $sized->height <= $height){
            return $sized;
        }
        
        $uid = uniqid('image');
        $basePath = $this->getBasePath();
        
        /**
         * Si se llega a este punto, es porque la imagen tiene la misma relación de aspecto
         * buscada, pero es más grande, por lo que debemos reducirla.
         */

        //Fix issue con imagenes migradas
        if(file_exists($basePath.DIRECTORY_SEPARATOR.$sized->relative_url)){
            $newSized = $this->newSized($width, $height, $basePath.DIRECTORY_SEPARATOR.$sized->relative_url);
            return $newSized;
        }
        
        return false;
        
    }
    
    public function newSized($width, $height, $sourcePath = null)
    {
        $sized = new Sized();
        $sized->media_id = $this->media_id;

        $basePath = $this->getBasePath();
        $directory = $this->getDirectory();
            
        FileHelper::createDirectory($basePath.DIRECTORY_SEPARATOR.$directory, 0775, true);
        
        $uid = uniqid('image');
        
        $relativePath = $directory . $uid . '.' . $this->extension;
        $fullPath = $basePath .DIRECTORY_SEPARATOR. $directory . $uid . '.' . $this->extension;



        //Modo de generacion de miniatura:
        //http://imagine.readthedocs.org/en/v0.2-0/image.html
        $thumbnailMode = Config::getValue('image_thumbnail_mode_inset') ? 
                \Imagine\Image\ManipulatorInterface::THUMBNAIL_INSET : 
                \Imagine\Image\ManipulatorInterface::THUMBNAIL_OUTBOUND;
        
        if($sourcePath === null){
            $sourcePath = $basePath.DIRECTORY_SEPARATOR.$this->relative_url;
        }
        
        //TODO: param quality
        \yii\imagine\Image::thumbnail($sourcePath, (int)$width, (int)$height, $thumbnailMode)->save($fullPath, ['quality' => 60]);
        
        $sized->relative_url = $relativePath;
        
        $size = getimagesize($fullPath);
        
        $sized->width = $size[0];
        $sized->height = $size[1];
        
        $sized->save();
        
        return $sized;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSizeds()
    {
        return $this->hasMany(Sized::className(), ['media_id' => 'media_id'])->andWhere('sized.width>0')->andWhere('sized.height>0');
    }
    
    public function fields() {
        $fields = parent::fields();
        
        return array_merge($fields, [
            'thumbnail' => function($model){
                return $model->getSizedUrl(300, 300);
            },
            'landscape_thumbnail' => function($model){
                return $model->getSizedUrl(416, 234);
            }
        ]);
    }
    
    /**
     * Devuelve un histograma basado en tolerancia. Aproxima el color al color
     * mas bajo de acuerdo a la tolerancia. 
     * @param float $factor
     * @param float $tolerance
     * @return array
     */
    public function histogram($factor = 60, $tolerance = 0.1)
    {
        if($factor < 1 || $tolerance >= 0.8 || $tolerance == 0){
            return false;
        }
        
        $colors = [];
     
        $img = \yii\imagine\Image::getImagine()->open(Yii::getAlias('@frontend').DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.$this->relative_url);
        $size = $img->getSize();
        
        $width = $size->getWidth();
        $height = $size->getHeight();
        
        $aspect = $width/$height;
        
        //Calculamos los pasos
        $rowStep = ($height / $aspect) / $factor;
        $colStep = ($width * $aspect) / $factor;
        
        //Obtenemos la cantidad de veces que aparece cada color en los ptos analizados
        for($i = $colStep/2; $i < $width; $i+=$colStep){
            for($j = $rowStep/2; $j < $height; $j+=$rowStep){
                
                $x = (int)$i;
                $y = (int)$j;
                
                $color = $img->getColorAt(new \Imagine\Image\Point($x,$y));
                
                //Calculamos rgb con tolerancia
                list($r, $g, $b) = [
                    $this->getRGBComponent($color->getRed(), $color->getAlpha(), $tolerance), 
                    $this->getRGBComponent($color->getGreen(), $color->getAlpha(), $tolerance), 
                    $this->getRGBComponent($color->getBlue(), $color->getAlpha(), $tolerance),
                ];
                
                if(isset($colors["$r,$g,$b"])){
                    $colors["$r,$g,$b"]['count'] += 1;
                }else{
                    $colors["$r,$g,$b"]['count'] = 1;
                    $colors["$r,$g,$b"]['color'] = new \common\modules\media\components\helpers\Color([
                        'r' => $color->getRed(),
                        'g' => $color->getGreen(),
                        'b' => $color->getBlue()
                    ]);
                }
                 
            }
        }

        //Ordenamos por orden de apariciones (orden inverso)
        uasort($colors, function($a,$b){ return $b['count']-$a['count']; });
        
        return $colors;
        
    }
    
    /**
     * Toma el histograma, quita el color de fondo y elimina aquellos colores
     * que no aparecen mas de determinadas veces, basado en la tolerancia. Para
     * determinar la cantidad de veces que debe aparecer un color para ser 
     * conservado, se utiliza la siguiente formula:
     * 
     * ($pxs / count($histogram)) * $tolerance;
     * 
     * donde $pxs es la cantidad total de puntos analizados, count($histogram)
     * es la cantidad total de colores y $tolerance es la tolerancia admitida. 
     * 
     * @param int $factor
     * @param float $tolerance
     * @param string $algorithm
     * @return type
     */
    public function smartHistogram($factor = 60, $tolerance = 0.1, $algorithm = 'histogram')
    {
        if(!in_array($algorithm, ['histogram', 'proximityHistogram'])){
            throw new \yii\web\HttpException(500, 'Algorithm not recognized.');
        }
        
        $img = \yii\imagine\Image::getImagine()->open(Yii::getAlias('@frontend').DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.$this->relative_url);
        $size = $img->getSize();
        
        $width = $size->getWidth();
        $height = $size->getHeight();
        
        //Obtenemos el histograma de acuerdo al algoritmo elegido
        $histogram = $this->$algorithm($factor, $tolerance);
        //Obtenemos el color de fondo
        $bg = $this->getBackgroundRGBA(3, 2, $tolerance);
        $bg_rgb = $this->getRGBComponent($bg->r, $bg->alpha, 0).','.
                $this->getRGBComponent($bg->g, $bg->alpha, 0).','.
                $this->getRGBComponent($bg->b, $bg->alpha, 0);
        
        //Quitamos el color de fondo del histograma
        if(isset($histogram[$bg_rgb])){
            unset($histogram[$bg_rgb]);
        }
        
        //Calculamos la cantidad de pixels analizados
        $pxs = 0;
        foreach($histogram as $info){
            $pxs += $info['count'];
        }        
        
        //Solo conservamos los colores que aparecen al menos $limit veces
        $limit = ($pxs / count($histogram)) * $tolerance;
        $counter = 0;
        foreach ($histogram as $info){
            if($counter > 0 && $info['count'] < $limit){
                $histogram = array_slice($histogram, 0, $counter, true);
            }
            $counter++;
        }
        
        return $histogram;
    }
    
    /**
     * Devuelve el color rgb equivalente al color original antepuesto sobre un fondo
     * blanco, de acuerdo al valor de alpha, y aproximado al valor mas cercano
     * de acuerdo a la tolerancia.
     * @param int $value
     * @param float $tolerance
     * @return int
     */
    private function getRGBComponent($value, $alpha, $tolerance)
    {
        $alpha = $alpha / 100;
        if(Yii::$app->params['Image[invertAlpha]']){
            $value = (int)(($alpha * 255) + ((1-$alpha) * $value));
        }else{
            $value = (int)(((1-$alpha) * 255) + ($alpha * $value));
        }
        
        if($tolerance < 0 || $value < (255 * $tolerance) ){
            return 0;
        }
        
        if($value > (255-(255 * $tolerance))){
            return 255;
        }
        
        $step = (int)($tolerance * 255) ? (int)($tolerance * 255) : 1;
        
        return $value - ($value % $step);
    }
    
    
    /**
     * Devuelve un histograma basado en la proximidad de los colores. Promedia
     * los colores similares a medida que analiza los pixels. Al finalizar, 
     * verifica que no hayan quedado colores similares luego de las aproximaciones,
     * y en tal caso, los quita.
     * 
     * @param float $factor
     * @param float $tolerance
     * @return array
     */
    public function proximityHistogram($factor = 60, $tolerance = 0.2)
    {
        if($factor < 1 || $tolerance >= 0.8 || $tolerance == 0){
            return false;
        }
        
        $colors = [];
     
        $img = \yii\imagine\Image::getImagine()->open(Yii::getAlias('@frontend').DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.$this->relative_url);
        $size = $img->getSize();
        
        $width = $size->getWidth();
        $height = $size->getHeight();
        
        $aspect = $width/$height;
        
        //Calculamos los pasos
        $rowStep = ($height / $aspect) / $factor;
        $colStep = ($width * $aspect) / $factor;
        
        //Obtenemos la cantidad de veces que aparece cada color en los ptos analizados
        for($i = $colStep/2; $i < $width; $i+=$colStep){
            for($j = $rowStep/2; $j < $height; $j+=$rowStep){
                
                $x = (int)$i;
                $y = (int)$j;
                
                $color = $img->getColorAt(new \Imagine\Image\Point($x,$y));
                
                if(!Yii::$app->params['Image[invertAlpha]'] && $color->getAlpha() > 0 || Yii::$app->params['Image[invertAlpha]'] && $color->getAlpha() < 100){
                    //Calculamos rgb con tolerancia
                    $rgb = new \common\modules\media\components\helpers\Color([
                        'r' => $color->getRed(),
                        'g' => $color->getGreen(),
                        'b' => $color->getBlue()
                    ]);
                    
                    $this->proximity($colors, $rgb, $tolerance);
                }
                 
            }
        }
        
        /**
         * Se verifican los colores, dado que al promediar pueden quedar
         * colores cercanos. En este caso, debe conservarse solo uno.
         */
        $verified = [];
        foreach($colors as $k => &$info){
            
            $color = $info['color'];
                    
            $color->r = (int)$color->r;
            $color->g = (int)$color->g;
            $color->b = (int)$color->b;
            
            $new = true;
            foreach($verified as $data){
                if($this->isNearColor($data['color'], $color, $tolerance)){
                    $new = false;
                    $data['count'] += $info['count'];
                }
            }
            
            if($new){
                $verified[$k]['count'] = $info['count'];
                $verified[$k]['color'] = $color;
            }
        }
        
        //Ordenamos por orden de apariciones (orden inverso)
        uasort($verified, function($a,$b){ return $b['count']-$a['count']; });
        return $verified;
        
    }
    
    /**
     * Determina la proximidad de un color a alguno de los colores ya analizados.
     * Si se aproxima a alguno, promedia el color y incrementa el contador.
     * Si no se aproxima a ninguno, agrega el color a la lista.
     * @param type $colors
     * @param type $rgb
     * @param type $tolerance
     * @return type
     */
    public function proximity(&$colors, $rgb, $tolerance)
    {
        
        foreach($colors as $key => &$info){
            $color = $info['color'];
            if($this->isNearColor($rgb, $color, $tolerance)){

                //Promedio
                $color->r = ($rgb->r + ($color->r * $info['count'])) / ($info['count'] + 1);
                $color->g = ($rgb->g + ($color->g * $info['count'])) / ($info['count'] + 1);
                $color->b = ($rgb->b + ($color->b * $info['count'])) / ($info['count'] + 1);
                $color->alpha = ($rgb->alpha + ($color->alpha * $info['count'])) / ($info['count'] + 1);

                $info['count']+=1;
                
                return;
            }
        }
        
        $colors["$rgb->r,$rgb->g,$rgb->b"] = [
            'count' => 1,
            'color' => new \common\modules\media\components\helpers\Color([
                'r' => $rgb->r,
                'g' => $rgb->g,
                'b' => $rgb->b,
                'alpha' => $rgb->alpha
            ])
        ];
        
    }
    
    /**
     * Verifica si un color es similar a otro color
     * @param array $rgb
     * @param array $rgb2
     * @param float $tolerance
     * @return boolean
     */
    public function isNearColor($rgb, $rgb2, $tolerance)
    {
        
        $tolerated = 255 * $tolerance;
        
        if($rgb->r > $rgb2->r - $tolerated && $rgb->r < $rgb2->r + $tolerated &&
            $rgb->g > $rgb2->g - $tolerated && $rgb->g < $rgb2->g + $tolerated &&
            $rgb->b > $rgb2->b - $tolerated && $rgb->b < $rgb2->b + $tolerated){
            
            return true;
        }else{
            return false;
        }
        
    }
    
    /**
     * Devuelve el color del fondo, analizando $lines de los limites de la imagen,
     * con un paso de $step (es decir, analiza N lineas, separadas por el paso determinado).
     * @param type $lines
     * @param type $step
     * @param type $tolerance
     * @return type
     */
    public function getBackgroundRGBA($lines = 3, $step = 2, $tolerance = 0.1)
    {
        
        $colors = [];
     
        $img = \yii\imagine\Image::getImagine()->open(Yii::getAlias('@frontend').DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.$this->relative_url);
        $size = $img->getSize();
        
        $width = $size->getWidth()-1;
        $height = $size->getHeight()-1;
        
        for($line = 1; $line <= $lines; $line++){
            for($i = 1; $i <= $height; $i+=$step){

                $x = 1;
                $y = (int)$i;

                $color = $img->getColorAt(new \Imagine\Image\Point($x,$y));
                
                //Calculamos rgb con tolerancia
                $rgb = new \quoma\media\components\helpers\Color([
                    'r' => $color->getRed(),
                    'g' => $color->getGreen(),
                    'b' => $color->getBlue(),
                    'alpha' => $color->getAlpha()
                ]);

                $this->proximity($colors, $rgb, $tolerance);

            }

            for($j = 1; $j <= $width; $j+=$step){

                $x = (int)$j;
                $y = 1;

                $color = $img->getColorAt(new \Imagine\Image\Point($x,$y));

                //Calculamos rgb con tolerancia
                $rgb = new \quoma\media\components\helpers\Color([
                    'r' => $color->getRed(),
                    'g' => $color->getGreen(),
                    'b' => $color->getBlue(),
                    'alpha' => $color->getAlpha()
                ]);

                $this->proximity($colors, $rgb, $tolerance);

            }
        }
        
        //Ordenamos por orden de apariciones (orden inverso)
        uasort($colors, function($a,$b){ return $b['count']-$a['count']; });
        $bg = array_shift($colors);
        
        $alpha = $bg['color']->alpha;
        if(Yii::$app->params['Image[invertAlpha]']){
            $alpha = 100 - $alpha; 
        }
        
        return new \quoma\media\components\helpers\Color(
            [
                'r' => (int)$bg['color']->r,
                'g' => (int)$bg['color']->g,
                'b' => (int)$bg['color']->b,
                'alpha' => $alpha
            ]
        );
        
    }
    
    /**
     * Si el color de fondo de la imagen es opaco, devuelve true. Si el color
     * de fondo es transparente, devuelve false.
     */
    public function getIsBgOpaque()
    {
        
        $bg = $this->getBackgroundRGBA(2, 1);
        $transparent = 100;
        if(Yii::$app->params['Image[invertAlpha]']){
            $transparent = 0;
        }
        
        if($bg->alpha != $transparent){
            return true;
        }
        
        return false;
        
    }
    
    public function renderButton($options = [], $params = [])
    {
        \quoma\media\components\image\OptimizerAssets::register(Yii::$app->view);
        return \quoma\media\components\upload\UploadWidget::widget([
            'type' => 'image', 
            'label' => '<span class="glyphicon glyphicon-plus"></span> '.Yii::t('app', 'Images'),
            'template' => '{input}',
            'buttonOptions' => $options,
            'extraParams' => $params
        ]); 
    }
    
    public function getUrl(){
        
        return $this->getSizedUrl($this->sourceWidth, $this->sourceHeight);
        
    }
    
    /**
     * Devuelve la url de la imagen original
     * @return string
     */
    public function getSourceUrl(){
        
        if(Config::getValue('cdn') == true){
            $baseUrl = Config::getValue('cdn_base_url');
        }else{
            $baseUrl = $this->base_url;
        }
        
        return $baseUrl .'/'. $this->relative_url;
        
    }
    
    public function customButtons($options = [])
    {
        if($this->isOptimized()){
            \yii\helpers\Html::addCssClass($options, 'btn btn-success');
            $label = Yii::t('app', 'Optimized');
        }else{
            \yii\helpers\Html::addCssClass($options, 'btn btn-default');
            $label = Yii::t('app', 'Optimize');
        }
        
        $options['data-optimize-media-id'] = $this->media_id;
        $options['data-media-action'] = $this->type;
        $options['data-media-url'] = \yii\helpers\Url::to(['/media/image/start-optimization', 'id' => $this->media_id]);
        return \yii\helpers\Html::a('<span class="glyphicon glyphicon-phone"></span> '.$label, "#$this->type", $options);
    }
    
    public function getAspect()
    {
        $width = $size->getWidth();
        $height = $size->getHeight();
        
        $aspect = $width/$height;
        
        return $aspect;
    }
    
    /**
     * Cuts a image
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     * @return boolean
     * @throws Exception
     */
    public function cutImage($x1,$y1,$x2,$y2){
        
        if($this->sourceWidth < $x2 || $this->sourceHeight < $y2)
            return false;
        
        $basePath = $this->getBasePath();
        
        //Creamos una imagen a partir del archivo. Utilizamos la funcion fromstring para evitar tener que evaluar la extension
        $img = imagecreatefromstring(file_get_contents($basePath.DIRECTORY_SEPARATOR.$this->relative_url));

        //Ancho y height de la nueva imagen
        $newWidth = (int)($x2 - $x1);
        $newHeight = (int)($y2 - $y1);
        
        if($newHeight < 0 || $newWidth < 0)
            throw new CHttpException(500,'Coorderadas inválidas.',11);

        //Nueva imagen
        $out = imagecreatetruecolor($newWidth, $newHeight);

        //Copiamos el area que queremos cortar
        $new = imagecopy ($out, $img, 0, 0, $x1, $y1, $newWidth, $newHeight);

        //Liberamos memoria
        imagedestroy($img);

        //Si todo salio bien, devolvemos el nuevo recurso imagen
        if($new)
            return $out;
        else
            return false;

    }
    
    /**
     * Efectua un crop y devuelve la url del tamaño requerido. Nunca se devuelve 
     * la imagen original por no estar comprimida.
     * @param int $width
     * @param  int $height
     * @return string url de la imagen
     * @throws \InvalidArgumentException
     */
    public function crop($width, $height, $start = [0,0])
    {
        if($width + $start[0] > $this->sourceWidth || $height + $start[1] > $this->sourceHeight){
            return false;
        }
        
        $sized = new Sized();
        $sized->media_id = $this->media_id;

        $basePath = $this->getBasePath();
        $directory = $this->getDirectory();
            
        FileHelper::createDirectory($basePath.DIRECTORY_SEPARATOR.$directory, 0775, true);
        
        $uid = uniqid('image');
        
        $relativePath = $directory . $uid . '.' . $this->extension;
        $fullPath = $basePath .DIRECTORY_SEPARATOR. $directory . $uid . '.' . $this->extension;
        
        //TODO: param quality
        \yii\imagine\Image::crop($basePath.DIRECTORY_SEPARATOR.$this->relative_url, $width, $height, $start)->save($fullPath, ['quality' => 75]);
        
        $sized->relative_url = $relativePath;
        
        $size = getimagesize($fullPath);
        
        $sized->width = $size[0];
        $sized->height = $size[1];
        
        $sized->save();
        return $sized->url;
        
    }
    
    //Al optimizar nuevamente una imagen, invalidamos las versiones anteriores con misma relación de aspecto
    public function invalidate($width, $height)
    {
        $aspect = $width/$height;
        
        //Tolerance: IF(width>height, +- ((height+1.25)/height)-1, +- ((width+1.25)/width)-1) -> 0.25 adicional por aproximación de valores flotantes binarios
        Sized::updateAll(['width' => null, 'height' => null], "media_id=$this->media_id "
                . "AND (IF(width>height,((height-1.25)/height),((width-1.25)/width))*(width/height)) <= $aspect "
                . "AND (IF(width>height,((height+1.25)/height),((width+1.25)/width))*(width/height)) >= $aspect "
                . "AND (width<>$this->sourceWidth OR height<>$this->sourceHeight)");
    }
    
    public function isOptimized()
    {
        
        $steps = static::getAspects();
        
        /**
         * Buscamos una imagen de iguales dimensiones o de igual relación de aspecto (con una mínima tolerancia).
         * Si la imagen tiene la misma relación de aspecto pero no las mismas dimensiones,
         * debemos reducirla.
         */
        foreach($steps as $step){
            
            $aspect = $step['aspect'];
            
            $width = $step['minWidth'];
            $height = $step['minHeight'];

            $aspectTolerance = $width > $height ? (($height+1.25)/$height)-1 : (($width+1.25)/$width)-1; //Tolerancia para relación de aspecto

            $toleratedWidth = $width-1; //Toleramos una diferencia de 1px
            $toleratedHeight = $height-1;
            
            if($this->sourceWidth < $width || $this->sourceHeight < $height){
                //Si la imagen es más pequeña que el minimo requerido por el step, validamos solo que se haya optimizado la relación de aspecto
                $condition = "(".(1-$aspectTolerance)."*(width/height)) <= $aspect AND (".(1+$aspectTolerance)."*(width/height)) >= $aspect";
            }else{
                $condition = "(".(1-$aspectTolerance)."*(width/height)) <= $aspect AND (".(1+$aspectTolerance)."*(width/height)) >= $aspect AND width>=$toleratedWidth AND height>=$toleratedHeight";
            }
            
            $sized = $this->getSizeds()->where(
                $condition
            )->exists();
            
            if(!$sized){
                return false;
            }
        
        }

        return true;
        
    }
    
    /**
     * Devuelve un array con relaciones de aspecto deseadas para optimizar las
     * imagenes.
     * @return type
     * @throws \yii\web\HttpException
     */
    public static function getAspects()
    {
        $aspectProvider = Yii::$app->aspectProvider;
        
        if($aspectProvider === null){
            throw new \yii\web\HttpException(500, 'You must add an aspect provider component to your configuration.');
        }
        
        return $aspectProvider->getAspects();
    }
    
    /**
     * Para corregir un problema asociado a migraciones en las que el ancho y
     * alto en la base de datos no es el correcto.
     */
    public function getSourceSize()
    {
        $basePath = $this->getBasePath();
        $path = $basePath.DIRECTORY_SEPARATOR.$this->relative_url;
        
        if(file_exists($path)){
            return getimagesize($path);
        }else{
            return [0,0];
        }
    }
    
    private $_sourceWidth;
    public function getSourceWidth()
    {
        if(!$this->_sourceWidth){
            $this->_sourceWidth = $this->getSourceSize()[0];
        }
        return $this->_sourceWidth;
    }
    private $_sourceHeight;
    public function getSourceHeight()
    {
        if(!$this->_sourceHeight){
            $this->_sourceHeight = $this->getSourceSize()[1];
        }
        return $this->_sourceHeight;
    }

}
