<?php

namespace wdmg\blog;

/**
 * Yii2 Blog
 *
 * @category        Module
 * @version         1.0.1
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-blog
 * @copyright       Copyright (c) 2019 W.D.M.Group, Ukraine
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
    private $version = "1.0.1";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 5;

    /**
     * @var string the default routes to render blog (use "/" - for root)
     */
    public $blogRoute = "/blog";
    public $blogCategoriesRoute = "/blog/categories";
    public $blogTagsRoute = "/blog/tags";

    /**
     * @var string, the default layout to render blog
     */
    public $blogLayout = "@app/views/layouts/main";

    /**
     * @var string, the default path to save blog thumbnails in @webroot
     */
    public $blogImagePath = "/uploads/blog";

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

        // Process and normalize route for blog in frontend
        $this->blogRoute = self::normalizeRoute($this->blogRoute);

        // Normalize path to image folder
        $this->blogImagePath = \yii\helpers\FileHelper::normalizePath($this->blogImagePath);
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
                    'url' => [$this->routePrefix . '/blog/list/'],
                    'active' => (in_array(\Yii::$app->controller->module->id, ['content']) &&  Yii::$app->controller->id == 'blocks'),
                ],
                [
                    'label' => Yii::t('app/modules/blog', 'Categories'),
                    'url' => [$this->routePrefix . '/blog/cats/'],
                    'active' => (in_array(\Yii::$app->controller->module->id, ['content']) &&  Yii::$app->controller->id == 'lists'),
                ],
                [
                    'label' => Yii::t('app/modules/blog', 'Tags list'),
                    'url' => [$this->routePrefix . '/blog/tags/'],
                    'active' => (in_array(\Yii::$app->controller->module->id, ['content']) &&  Yii::$app->controller->id == 'lists'),
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

        // Add routes to blog in frontend
        $blogRoute = $this->blogRoute;
        if (empty($blogRoute) || $blogRoute == "/") {
            $app->getUrlManager()->addRules([
                [
                    'pattern' => '/<alias:[\w-]+>',
                    'route' => 'admin/blog/default/view',
                    'suffix' => ''
                ],
                '/<alias:[\w-]+>' => 'admin/blog/default/view',
            ], true);
        } else {
            $app->getUrlManager()->addRules([
                [
                    'pattern' => $blogRoute,
                    'route' => 'admin/blog/default/index',
                    'suffix' => ''
                ],
                [
                    'pattern' => $blogRoute . '/<alias:[\w-]+>',
                    'route' => 'admin/blog/default/view',
                    'suffix' => ''
                ],
                $blogRoute => 'admin/blog/default/index',
                $blogRoute . '/<alias:[\w-]+>' => 'admin/blog/default/view',
            ], true);
        }
    }


    /**
     * {@inheritdoc}
     */
    public function install()
    {
        parent::install();
        $path = Yii::getAlias('@webroot') . $this->blogImagePath;
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
        $path = Yii::getAlias('@webroot') . $this->blogImagePath;
        if (\yii\helpers\FileHelper::removeDirectory($path))
            return true;
        else
            return false;
    }
}