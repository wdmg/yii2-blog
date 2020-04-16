<?php

namespace wdmg\blog;

/**
 * Yii2 Blog
 *
 * @category        Module
 * @version         1.2.1
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-blog
 * @copyright       Copyright (c) 2019 - 2020 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use Yii;
use wdmg\base\BaseModule;
use yii\helpers\ArrayHelper;

/**
 * Blog module definition class
 */
class Module extends BaseModule
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'wdmg\blog\controllers';

    /**
     * {@inheritdoc}
     */
    public $defaultRoute = "blog/list";

    /**
     * @var string, the name of module
     */
    public $name = "Blog";

    /**
     * @var string, the description of module
     */
    public $description = "Publications manager";

    /**
     * @var string the module version
     */
    private $version = "1.2.1";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 5;

    /**
     * @var string the default routes to render blog (use "/" - for root)
     */
    public $baseRoute = "/blog";
    public $catsRoute = "/blog/categories";
    public $tagsRoute = "/blog/tags";

    /**
     * @var string, the default layout to render blog
     */
    public $baseLayout = "@app/views/layouts/main";

    /**
     * @var string, the default path to save blog thumbnails in @webroot
     */
    public $imagePath = "/uploads/blog";

    /**
     * @var array, the list of support locales for multi-language versions of posts.
     * @note This variable will be override if you use the `wdmg\yii2-translations` module.
     */
    public $supportLocales = ['ru-RU', 'uk-UA', 'en-US'];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Set version of current module
        $this->setVersion($this->version);

        // Set priority of current module
        $this->setPriority($this->priority);

        // Process and normalize routes for blog in frontend
        $this->baseRoute = self::normalizeRoute($this->baseRoute);
        $this->catsRoute = self::normalizeRoute($this->catsRoute);
        $this->tagsRoute = self::normalizeRoute($this->tagsRoute);

        // Normalize path to image folder
        $this->imagePath = \yii\helpers\FileHelper::normalizePath($this->imagePath);
    }

    /**
     * {@inheritdoc}
     */
    public function dashboardNavItems($createLink = false)
    {
        $items = [
            'label' => $this->name,
            'url' => [$this->routePrefix . '/'. $this->id],
            'icon' => 'fa fa-fw fa-feather-alt',
            'active' => in_array(\Yii::$app->controller->module->id, [$this->id]),
            'items' => [
                [
                    'label' => Yii::t('app/modules/blog', 'All posts'),
                    'url' => [$this->routePrefix . '/blog/posts/'],
                    'active' => (in_array(\Yii::$app->controller->module->id, ['content']) &&  Yii::$app->controller->id == 'posts'),
                ],
                [
                    'label' => Yii::t('app/modules/blog', 'Categories'),
                    'url' => [$this->routePrefix . '/blog/cats/'],
                    'active' => (in_array(\Yii::$app->controller->module->id, ['content']) &&  Yii::$app->controller->id == 'cats'),
                ],
                [
                    'label' => Yii::t('app/modules/blog', 'Tags list'),
                    'url' => [$this->routePrefix . '/blog/tags/'],
                    'active' => (in_array(\Yii::$app->controller->module->id, ['content']) &&  Yii::$app->controller->id == 'tags'),
                ]
            ]
        ];
        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        parent::bootstrap($app);

        if (!$this->isBackend()) {

            // Get language scheme if available
            $custom = false;
            $hide = false;
            $scheme = null;
            if (isset(Yii::$app->translations)) {
                $custom = true;
                $hide = Yii::$app->translations->module->hideDefaultLang;
                $scheme = Yii::$app->translations->module->languageScheme;
            }

            // Add routes for frontend
            switch ($scheme) {
                case "after":

                    $app->getUrlManager()->addRules([
                        $this->baseRoute . '/<alias:[\w-]+>/<lang:\w+>' => 'admin/blog/default/view',
                        $this->baseRoute . '/<lang:\w+>' => 'admin/blog/default/index',
                    ], true);

                    if ($hide) {
                        $app->getUrlManager()->addRules([
                            $this->baseRoute . '/<alias:[\w-]+>' => 'admin/blog/default/view',
                            $this->baseRoute => 'admin/blog/default/index',
                        ], true);
                    }

                    break;

                case "query":

                    $app->getUrlManager()->addRules([
                        $this->baseRoute . '/<alias:[\w-]+>' => 'admin/blog/default/view',
                        $this->baseRoute => 'admin/blog/default/index',
                    ], true);

                    /*if ($hide) {

                    }*/

                    break;

                case "subdomain":

                    if ($host = $app->getRequest()->getHostName()) {
                        $app->getUrlManager()->addRules([
                            'http(s)?://' . $host. '/' . $this->baseRoute . '/<alias:[\w-]+>' => 'admin/pages/default/view',
                            'http(s)?://' . $host. '/' . $this->baseRoute => 'admin/pages/default/index',
                        ], true);

                        /*if ($hide) {

                        }*/
                    }

                    break;

                default:

                    $app->getUrlManager()->addRules([
                        '/<lang:\w+>' . $this->baseRoute . '/<alias:[\w-]+>' => 'admin/blog/default/view',
                        '/<lang:\w+>' . $this->baseRoute => 'admin/blog/default/index',
                    ], true);

                    if ($hide || !$custom) {
                        $app->getUrlManager()->addRules([
                            $this->baseRoute . '/<alias:[\w-]+>' => 'admin/blog/default/view',
                            $this->baseRoute => 'admin/blog/default/index',
                        ], true);
                    }

                    break;
            }
        }
    }


    /**
     * {@inheritdoc}
     */
    public function install()
    {
        parent::install();
        $path = Yii::getAlias('@webroot') . $this->imagePath;
        if (\yii\helpers\FileHelper::createDirectory($path, $mode = 0775, $recursive = true))
            return true;
        else
            return false;
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall()
    {
        parent::uninstall();
        $path = Yii::getAlias('@webroot') . $this->imagePath;
        if (\yii\helpers\FileHelper::removeDirectory($path))
            return true;
        else
            return false;
    }
}