<?php namespace RainLab\Builder\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Classes\ControllerModel;
use RainLab\Builder\Classes\PluginCode;
use ApplicationException;
use Exception;
use Request;
use Flash;
use Input;
use Lang;

/**
 * Plugin controller management functionality for the Builder index controller
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexControllerOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/rainlab/builder/classes/controllermodel/fields.yaml';

    public function onControllerOpen()
    {
        $controller = Input::get('controller');
        $pluginCodeObj = $this->getPluginCode();

        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $widget = $this->makeBaseFormWidget($controller, $options);
        $this->vars['controller'] = $controller;

        $result = [
            'tabTitle' => $this->getTabName($widget->model),
            'tabIcon' => 'icon-asterisk',
            'tabId' => $this->getTabId($pluginCodeObj->toCode(), $controller),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode()
            ])
        ];

        return $result;
    }

    public function onControllerCreate()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));

        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $model = $this->loadOrCreateBaseModel(null, $options);
        $model->fill($_POST);
        $model->save();
return "test";
        $widget = $this->makeBaseFormWidget($model->controller, $options);
        $this->vars['controller'] = $model->controller;

        $result = [
            'tabTitle' => $this->getTabName($widget->model),
            'tabIcon' => 'icon-asterisk',
            'tabId' => $this->getTabId($pluginCodeObj->toCode(), $controller),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode()
            ])
        ];

        return $result;
    }

    public function onControllerSave()
    {
        $controller = Input::get('controller');

        $model = $this->loadModelFromPost();
        $model->fill($_POST);
        $model->save();

        Flash::success(Lang::get('rainlab.builder::lang.controller.saved'));

        $result['builderRepsonseData'] = [];

        return $result;
    }

    public function onControllerShowCreatePopup()
    {
        $pluginCodeObj = $this->getPluginCode();

        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $this->baseFormConfigFile = '~/plugins/rainlab/builder/classes/controllermodel/new-controller-fields.yaml';
        $widget = $this->makeBaseFormWidget(null, $options);

        return $this->makePartial('create-controller-popup-form', [
            'form'=>$widget,
            'pluginCode' =>  $pluginCodeObj->toCode()
        ]);
    }

    protected function getTabName($model)
    {
        $pluginName = $model->getModelPluginName();

        return $pluginName.'/'.$model->controller;
    }

    protected function getTabId($pluginCode, $controller)
    {
        return 'controller-'.$pluginCode.'-'.$controller;
    }

    protected function loadModelFromPost()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));
        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $controller = Input::get('controller');

        return $this->loadOrCreateBaseModel($controller, $options);
    }

    protected function loadOrCreateBaseModel($controller, $options = [])
    {
        $model = new ControllerModel();

        if (isset($options['pluginCode'])) {
            $model->setPluginCode($options['pluginCode']);
        }

        if (!$controller) {
            return $model;
        }

        $model->load($controller);
        return $model;
    }
}