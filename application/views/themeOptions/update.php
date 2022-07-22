<?php
/* @var ThemeOptionsController $this */
/* @var TemplateConfiguration $model */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyTemplateOptionsUpdate');

?>
<?php if (empty($model->sid)) : ?>
    <!-- This is only visible when we're not in survey view. -->
    <div class='menubar surveybar' id='theme-options-bar'>
        <div class="container-fluid">
            <div class='row'>
                <div class='text-end'>

                    <?php
                    $sThemeOptionUrl = App()->createUrl("themeOptions");
                    $sGroupEditionUrl = App()->createUrl("admin/surveysgroups/sa/update", ["id" => $gsid, "#" => 'templateSettingsFortThisGroup']);
                    $sUrl = (is_null($gsid)) ? $sThemeOptionUrl : $sGroupEditionUrl;
                    ?>

                    <!-- Back -->
                    <a class="btn btn-outline-secondary" href="<?php echo $sUrl; ?>">
                        <span class="fa fa-backward"></span>
                        <?php eT('Back'); ?>
                    </a>

                    <!-- Save -->
                    <a class="btn btn-success" href="#" role="button" id="save-form-button" data-form-id="template-options-form">
                        <span class="fa fa-floppy-o"></span>
                        <?php eT('Save'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php else : ?>
<div class="col-12 side-body <?= getSideBodyClass(false) ?>" id="theme-option-sidebody">
    <?php endif; ?>
    <!-- Using bootstrap tabs to differ between just hte options and advanced direct settings -->
    <div class="row">
        <div class="col-12">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs test" id="theme-options-tabs" role="tablist">
                <?php if ($aOptionAttributes['optionsPage'] === 'core'): ?>
                    <?php foreach ($aOptionAttributes['categories'] as $key => $category): ?>
                        <li role="presentation" class="nav-item">
                            <button class="nav-link <?php echo $key == 0 ? 'active' : 'action_hide_on_inherit'; ?>" data-bs-target="#category-<?php echo $key; ?>" aria-controls="category-<?php echo $key; ?>" role="tab" data-bs-toggle="tab" aria-selected="<?php echo $key == 0 ? 'true' : 'false'; ?>">
                                <?php echo $category; ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li role="presentation" class="nav-item">
                        <button class="nav-link active" data-bs-target="#simple" aria-controls="home" role="tab" data-bs-toggle="tab" aria-selected="true">
                            <?php eT('Simple options') ?>
                        </button>
                    </li>
                <?php endif; ?>
                <li role="presentation" class="nav-item">
                    <button class="nav-link <?php echo Yii::app()->getConfig('debug') > 1 ? '' : 'd-none'; ?>" data-bs-target="#advanced" aria-controls="profile" role="tab" data-bs-toggle="tab" aria-selected="false">
                        <?php eT('Advanced options') ?>
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <form class='form action_update_options_string_form' action=''>
                <?php echo TbHtml::submitButton($model->isNewRecord ? gT('Create') : gT('Save'), ['id' => 'theme-options--submit', 'class' => 'd-none action_update_options_string_button']); ?>
            <!-- Tab panes -->
                <div class="tab-content">
            <?php /* Begin theme option form */ ?>
                    <?php
                    /*
                     * Here we render just the options as a simple form.
                     * On save, the options are parsed to a JSON string and put into the relevant field in the "real" form
                     * before saving that to database.
                     */

                    //First convert options to json and check if it is valid
                    $oOptions = json_decode($model->options);
                    $jsonError = json_last_error();
                    //if it is not valid, render message
                    if ($jsonError !== JSON_ERROR_NONE && $model->options !== 'inherit') {
                        //return
                        echo "<div class='ls-flex-column fill'><h4>" . gT('There are no simple options in this survey theme.') . "</h4></div>";
                    } else {
                        //if however there is no error in the parsing of the json string go forth and render the form
                        /*
                         * The form element needs to hold the class "action_update_options_string_form" to be correctly bound
                         * To be able to change the value in the "real" form, the input needs to now what to change.
                         * So the name attribute should contain the object key we want to change
                         */

                        if ($aOptionAttributes['optionsPage'] == 'core') {
                            $this->renderPartial(
                                './options_core',
                                [
                                    'aOptionAttributes'      => $aOptionAttributes,
                                    'aTemplateConfiguration' => $aTemplateConfiguration,
                                    'oParentOptions'         => $oParentOptions,
                                    'sPackagesToLoad'        => $sPackagesToLoad
                                ]
                            );
                        } else {
                            echo '<div role="tabpanel" class="tab-pane active" id="simple">';
                            echo $templateOptionPage;
                            echo '</div>';
                        }
                    }
                    ?>
                <?php $this->renderPartial(
                    '/themeOptions/advanced',
                    [
                        'model' => $model,
                        'optionInheritedValues' => $optionInheritedValues,
                        'optionCssFiles' => $optionCssFiles,
                        'optionCssFramework' => $optionCssFramework
                    ]
                ); ?>
            </div>
            <!-- End form tag -->
            </form>
        </div>
    </div>
    <?php if (!empty($model->sid)) : // If we are in survey view, we have an additional div that we need to close    ?>
</div>
<?php endif; ?>

<!-- Form for image file upload -->
<div class="d-none">
    <?php echo TbHtml::form(['admin/themes/sa/upload'], 'post', ['id' => 'upload_frontend', 'name' => 'upload_frontend', 'enctype' => 'multipart/form-data']); ?>
    <?php if (isset($aTemplateConfiguration['sid']) && !empty($aTemplateConfiguration['sid'])) : ?>
        <input type='hidden' name='surveyid' value='<?= $aTemplateConfiguration['sid'] ?>'/>
    <?php endif; ?>
    <input type='hidden' name='templatename' value='<?php echo $aTemplateConfiguration['template_name']; ?>'/>
    <input type='hidden' name='templateconfig' value='<?php echo $aTemplateConfiguration['id']; ?>'/>
    <input type='hidden' name='action' value='templateuploadimagefile'/>
    <?php echo TbHtml::endForm() ?>
</div>

<?php
Yii::app()->getClientScript()->registerScript(
    "themeoptions-scripts",
    '

        var bindUpload = function(options){
            var $activeForm = $(options.form);
            var $activeInput = $(options.input);
            var $progressBar = $(options.progress);

            var onSuccess = options.onSuccess || function(){};
            var onBeforeSend = options.onBeforeSend || function(){};

            var progressHandling = function(event){
                var percent = 0;
                var position = event.loaded || event.position;
                var total = event.total;
                if (event.lengthComputable) {
                    percent = Math.ceil(position / total * 100);
                }
                // update progressbars classes so it fits your code
                $progressBar.css("width", String(percent)+"%");
                $progressBar.find(\'span.sr-only\').text(percent + "%");
            };

            $activeInput.on(\'change\', function(e){
                e.preventDefault();
                var formData = new FormData($activeForm[0]);
                console.log(JSON.stringify(formData));
                // add assoc key values, this will be posts values
                formData.append("file", $activeInput.prop(\'files\')[0]);

                $.ajax({
                    type: "POST",
                    url: $activeForm.attr(\'action\'),
                    xhr: function () {
                        var myXhr = $.ajaxSettings.xhr();
                        if (myXhr.upload) {
                            myXhr.upload.addEventListener(\'progress\', progressHandling, false);
                        }
                        return myXhr;
                    },
                    beforeSend : onBeforeSend,
                    success: function (data) {
                        console.log(data);
                        if(data.success === true){
                            $(\'#notif-container\').append(\'<div class="alert alert-success" role="alert"><button type="button" class="close" data-bs-dismiss="alert" aria-label="Close"></button>\'+data.message+\'</div>\');
                            $progressBar.css("width", "0%");
                            $progressBar.find(\'span.sr-only\').text(\'0%\');
                            onSuccess();
                        } else {
                            $(\'#notif-container\').append(\'<div class="alert alert-danger" role="alert"><button type="button" class="close" data-bs-dismiss="alert" aria-label="Close"></button>\'+data.message+\'</div>\');
                            $progressBar.css("width", "0%");
                            $progressBar.find(\'span.sr-only\').text(\'0%\');
                        }
                    },
                    error: function (error) {
                        $progressBar.css("width", "0%");
                        $progressBar.find(\'span.sr-only\').text(\'0%\');
                        console.log(error);
                    },
                    async: true,
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    timeout: 60000
                });
            });
            return this;
        };

        $(document).on(\'ready pjax:scriptcomplete\', function(){
            $("#theme-option-sidebody").height($("#advanced").height()+200);
            var uploadImageBind = new bindUpload({
                form: \'#uploadimage\',
                input: \'#upload_image\',
                progress: \'#upload_progress\'
            });
            $("#theme-options-tabs li a").click(function(e){
                if ($(this).attr("href") == "#advanced"){
                    $("#advanced").show();
                    $("#simple").hide();
                    $("[id^=category-]").hide();
                } else {
                    $("#advanced").hide();
                    $("#simple").show();
                    $("[id^=category-]").hide();
                    $($(this).attr("href")).show();
                }
            });
            

        });
    ',
    LSYii_ClientScript::POS_END
);
?>
