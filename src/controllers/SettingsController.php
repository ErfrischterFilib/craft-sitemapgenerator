<?php

namespace filib\sitemapgenerator\controllers;

use Craft;
use craft\errors\MissingComponentException;
use craft\web\Controller;
use filib\sitemapgenerator\helpers\Update;
use filib\sitemapgenerator\SitemapGenerator;
use yii\web\BadRequestHttpException;
use yii\web\MethodNotAllowedHttpException;

class SettingsController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    /**
     * @throws MethodNotAllowedHttpException
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     */
    public function actionSave(): \yii\web\Response
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $newSettings = $request->getBodyParam('settings', []);
        $plugin = SitemapGenerator::$plugin;
        $plugin->setSettings([
            'entrySettings' => $newSettings
        ]);
        $migration = new Update();
        $migration->updateSettings($newSettings);
        $generator = new SitemapController('settings', Craft::$app);
        $generator->actionGenerateSitemap();
        Craft::$app->getSession()->setNotice('Settings saved successfully.');
        return $this->redirectToPostedUrl();
    }
}