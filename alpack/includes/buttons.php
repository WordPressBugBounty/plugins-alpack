<?php
if (!defined('ABSPATH')) {
    exit;
}

function presslearn_is_plugin_active_for_buttons() {
    $is_activated = false;
    if (function_exists('presslearn_plugin')) {
        $is_activated = presslearn_plugin()->is_plugin_activated();
    }
    return $is_activated;
}

function presslearn_register_quick_button_block() {
    if (!presslearn_is_plugin_active_for_buttons()) {
        return;
    }
    
    $quick_button_enabled = get_option('presslearn_quick_button_enabled', 'no');
    if ($quick_button_enabled !== 'yes') {
        return;
    }
    
    wp_register_script(
        'presslearn-quick-button-block',
        false,
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
        time(),
        true
    );
    
    $button_transition_enabled = get_option('presslearn_button_transition_enabled', 'no');
    
    $saved_preset = get_option('presslearn_button_preset', array());
    $saved_preset_json = !empty($saved_preset) ? wp_json_encode($saved_preset) : 'null';
    
    wp_add_inline_script('presslearn-quick-button-block', '
        (function(blocks, element, components, editor) {
            var el = element.createElement;
            var RichText = editor.RichText;
            var ColorPalette = components.ColorPalette;
            var InspectorControls = editor.InspectorControls;
            var TextControl = components.TextControl;
            var SelectControl = components.SelectControl;
            var PanelBody = components.PanelBody;
            var PanelRow = components.PanelRow;
            
            var buttonIcon = el("svg", { width: 24, height: 24, viewBox: "0 0 24 24" },
                el("path", { 
                    d: "M19 6H5c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 10H5V8h14v8z",
                    fill: "#2196F3"
                })
            );
            
            var infiniteAnimationEnabled = "' . esc_js($button_transition_enabled === 'yes' ? 'yes' : 'no') . '";
            
            var savedPreset = ' . $saved_preset_json . ';
            var presslearn_ajax = {
                ajaxurl: "' . esc_js(admin_url('admin-ajax.php')) . '",
                nonce: "' . esc_js(wp_create_nonce('presslearn_button_preset')) . '"
            };
            
            var defaultValues = {
                buttonText: "버튼 텍스트",
                buttonUrl: "#",
                buttonColor: savedPreset && savedPreset.buttonColor ? savedPreset.buttonColor : "#2196F3",
                buttonTextColor: savedPreset && savedPreset.buttonTextColor ? savedPreset.buttonTextColor : "#ffffff",
                buttonHoverColor: savedPreset && savedPreset.buttonHoverColor ? savedPreset.buttonHoverColor : "",
                buttonPosition: savedPreset && savedPreset.buttonPosition ? savedPreset.buttonPosition : "center",
                buttonSize: savedPreset && savedPreset.buttonSize ? savedPreset.buttonSize : "medium",
                buttonWidth: savedPreset && savedPreset.buttonWidth ? savedPreset.buttonWidth : "default",
                openInNewTab: false,
                buttonAnimation: savedPreset && savedPreset.buttonAnimation ? savedPreset.buttonAnimation : "none",
                infiniteAnimation: infiniteAnimationEnabled,
                borderRadius: savedPreset && savedPreset.borderRadius ? savedPreset.borderRadius : 4
            };
            
            blocks.registerBlockType("presslearn/quick-button", {
                title: "PL 빠른 버튼",
                icon: buttonIcon,
                category: "presslearn",
                keywords: ["버튼", "button", "link", "presslearn"],
                description: "클릭 가능한 버튼을 추가합니다. 텍스트, URL, 색상 및 정렬을 사용자 정의할 수 있습니다.",
                deprecated: [{
                    attributes: {
                        buttonText: { type: "string", default: "버튼 텍스트" },
                        buttonUrl: { type: "string", default: "#" },
                        buttonColor: { type: "string", default: "#2196F3" },
                        buttonTextColor: { type: "string", default: "#ffffff" },
                        buttonHoverColor: { type: "string", default: "" },
                        buttonPosition: { type: "string", default: "center" },
                        buttonSize: { type: "string", default: "medium" },
                        buttonWidth: { type: "string", default: "default" },
                        openInNewTab: { type: "boolean", default: false },
                        buttonAnimation: { type: "string", default: "none" },
                        infiniteAnimation: { type: "string", default: "yes" },
                        borderRadius: { type: "number", default: 4 }
                    },
                    save: function(props) {
                        var attributes = props.attributes;
                        var buttonPadding = "12px 24px";
                        switch(attributes.buttonSize) {
                            case "small": buttonPadding = "8px 16px"; break;
                            case "large": buttonPadding = "16px 32px"; break;
                            case "xlarge": buttonPadding = "20px 40px"; break;
                        }
                        var fontSize = "15px";
                        switch(attributes.buttonSize) {
                            case "small": fontSize = "13px"; break;
                            case "large": fontSize = "18px"; break;
                            case "xlarge": fontSize = "22px"; break;
                        }
                        var buttonWidth = "auto";
                        var textAlign = "inherit";
                        if (attributes.buttonWidth === "half") {
                            buttonWidth = "50%";
                            textAlign = "center";
                        } else if (attributes.buttonWidth === "full") {
                            buttonWidth = "100%";
                            textAlign = "center";
                        }
                        var containerAlignment = attributes.buttonPosition;
                        if (attributes.buttonWidth === "full") {
                            containerAlignment = "center";
                        }
                        var animationClass = "presslearn-button presslearn-button-animation-" + attributes.buttonAnimation;
                        if (infiniteAnimationEnabled === "yes" && attributes.buttonAnimation !== "none") {
                            animationClass += " presslearn-button-animation-infinite";
                        }
                        return el("div", { 
                            className: "presslearn-button-container",
                            style: { textAlign: containerAlignment, margin: "20px 0" }
                        },
                            el("a", {
                                className: animationClass,
                                href: attributes.buttonUrl || "#",
                                ...(attributes.openInNewTab ? { target: "_blank", rel: "noopener noreferrer" } : {}),
                                style: {
                                    display: "inline-block",
                                    padding: buttonPadding,
                                    backgroundColor: attributes.buttonColor,
                                    color: attributes.buttonTextColor,
                                    textDecoration: "none",
                                    borderRadius: attributes.borderRadius + "px",
                                    fontWeight: "bold",
                                    fontSize: fontSize,
                                    width: buttonWidth,
                                    textAlign: textAlign,
                                    boxSizing: "border-box",
                                    transition: "all 0.3s ease-in-out"
                                },
                                "data-hover-color": attributes.buttonHoverColor || attributes.buttonColor
                            }, attributes.buttonText || "버튼 텍스트")
                        );
                    }
                }],
                attributes: {
                    buttonText: {
                        type: "string",
                        default: defaultValues.buttonText
                    },
                    buttonUrl: {
                        type: "string",
                        default: defaultValues.buttonUrl
                    },
                    buttonColor: {
                        type: "string",
                        default: defaultValues.buttonColor
                    },
                    buttonTextColor: {
                        type: "string",
                        default: defaultValues.buttonTextColor
                    },
                    buttonHoverColor: {
                        type: "string",
                        default: defaultValues.buttonHoverColor
                    },
                    buttonPosition: {
                        type: "string",
                        default: defaultValues.buttonPosition
                    },
                    buttonSize: {
                        type: "string",
                        default: defaultValues.buttonSize
                    },
                    buttonWidth: {
                        type: "string",
                        default: defaultValues.buttonWidth
                    },
                    openInNewTab: {
                        type: "boolean",
                        default: defaultValues.openInNewTab
                    },
                    buttonAnimation: {
                        type: "string",
                        default: defaultValues.buttonAnimation
                    },
                    infiniteAnimation: {
                        type: "string",
                        default: defaultValues.infiniteAnimation
                    },
                    borderRadius: {
                        type: "number",
                        default: defaultValues.borderRadius
                    }
                },
                example: {
                    attributes: {
                        buttonText: "버튼 미리보기",
                        buttonColor: "#2196F3",
                        buttonPosition: "center"
                    }
                },
                edit: function(props) {
                    var attributes = props.attributes;
                    
                    function onChangeText(newText) {
                        props.setAttributes({ buttonText: newText });
                    }
                    
                    function onChangeUrl(newUrl) {
                        props.setAttributes({ buttonUrl: newUrl });
                    }
                    
                    function onChangeColor(newColor) {
                        props.setAttributes({ buttonColor: newColor });
                    }
                    
                    function onChangeTextColor(newColor) {
                        props.setAttributes({ buttonTextColor: newColor });
                    }
                    
                    function onChangeHoverColor(newColor) {
                        props.setAttributes({ buttonHoverColor: newColor });
                    }
                    
                    function onChangePosition(newPosition) {
                        props.setAttributes({ buttonPosition: newPosition });
                    }
                    
                    function onChangeSize(newSize) {
                        props.setAttributes({ buttonSize: newSize });
                    }
                    
                    function onChangeWidth(newWidth) {
                        props.setAttributes({ buttonWidth: newWidth });
                    }
                    
                    function onChangeOpenInNewTab(newValue) {
                        props.setAttributes({ openInNewTab: newValue });
                    }
                    
                    function onChangeAnimation(newAnimation) {
                        props.setAttributes({ buttonAnimation: newAnimation });
                    }
                    
                    function onChangeBorderRadius(newRadius) {
                        props.setAttributes({ borderRadius: newRadius });
                    }
                    
                    function savePreset() {
                        var presetData = {
                            buttonColor: attributes.buttonColor,
                            buttonTextColor: attributes.buttonTextColor,
                            buttonHoverColor: attributes.buttonHoverColor,
                            buttonPosition: attributes.buttonPosition,
                            buttonSize: attributes.buttonSize,
                            buttonWidth: attributes.buttonWidth,
                            buttonAnimation: attributes.buttonAnimation,
                            borderRadius: attributes.borderRadius
                        };
                        
                        wp.ajax.post(\'presslearn_save_button_preset\', {
                            preset: JSON.stringify(presetData),
                            nonce: presslearn_ajax.nonce
                        }).done(function(response) {
                            savedPreset = presetData;
                            window.presslearnButtonPreset = presetData;
                            
                            defaultValues.buttonColor = presetData.buttonColor;
                            defaultValues.buttonTextColor = presetData.buttonTextColor;
                            defaultValues.buttonHoverColor = presetData.buttonHoverColor;
                            defaultValues.buttonPosition = presetData.buttonPosition;
                            defaultValues.buttonSize = presetData.buttonSize;
                            defaultValues.buttonWidth = presetData.buttonWidth;
                            defaultValues.buttonAnimation = presetData.buttonAnimation;
                            defaultValues.borderRadius = presetData.borderRadius;
                            
                            var blockType = blocks.getBlockType(\'presslearn/quick-button\');
                            if (blockType && blockType.attributes) {
                                blockType.attributes.buttonColor.default = presetData.buttonColor;
                                blockType.attributes.buttonTextColor.default = presetData.buttonTextColor;
                                blockType.attributes.buttonHoverColor.default = presetData.buttonHoverColor;
                                blockType.attributes.buttonPosition.default = presetData.buttonPosition;
                                blockType.attributes.buttonSize.default = presetData.buttonSize;
                                blockType.attributes.buttonWidth.default = presetData.buttonWidth;
                                blockType.attributes.buttonAnimation.default = presetData.buttonAnimation;
                                blockType.attributes.borderRadius.default = presetData.borderRadius;
                            }
                            
                            alert(\'프리셋이 저장되었습니다. 새로운 버튼에 적용됩니다.\');
                        }).fail(function(error) {
                            alert(\'프리셋 저장에 실패했습니다.\');
                        });
                    }
                    
                    function deletePreset() {
                        if (confirm(\'저장된 프리셋을 삭제하시겠습니까?\')) {
                            wp.ajax.post(\'presslearn_delete_button_preset\', {
                                nonce: presslearn_ajax.nonce
                            }).done(function(response) {
                                savedPreset = null;
                                window.presslearnButtonPreset = null;
                                
                                defaultValues.buttonColor = "#2196F3";
                                defaultValues.buttonTextColor = "#ffffff";
                                defaultValues.buttonHoverColor = "";
                                defaultValues.buttonPosition = "center";
                                defaultValues.buttonSize = "medium";
                                defaultValues.buttonWidth = "default";
                                defaultValues.buttonAnimation = "none";
                                defaultValues.borderRadius = 4;
                                
                                var blockType = blocks.getBlockType(\'presslearn/quick-button\');
                                if (blockType && blockType.attributes) {
                                    blockType.attributes.buttonColor.default = "#2196F3";
                                    blockType.attributes.buttonTextColor.default = "#ffffff";
                                    blockType.attributes.buttonHoverColor.default = "";
                                    blockType.attributes.buttonPosition.default = "center";
                                    blockType.attributes.buttonSize.default = "medium";
                                    blockType.attributes.buttonWidth.default = "default";
                                    blockType.attributes.buttonAnimation.default = "none";
                                    blockType.attributes.borderRadius.default = 4;
                                }
                                
                                alert(\'프리셋이 삭제되었습니다. 새로운 버튼은 기본값으로 생성됩니다.\');
                            }).fail(function(error) {
                                alert(\'프리셋 삭제에 실패했습니다.\');
                            });
                        }
                    }
                    
                    var buttonPadding;
                    switch(attributes.buttonSize) {
                        case "small":
                            buttonPadding = "8px 16px";
                            break;
                        case "large":
                            buttonPadding = "16px 32px";
                            break;
                        case "xlarge":
                            buttonPadding = "20px 40px";
                            break;
                        default: // medium
                            buttonPadding = "12px 24px";
                    }
                    
                    var fontSize;
                    switch(attributes.buttonSize) {
                        case "small":
                            fontSize = "13px";
                            break;
                        case "large":
                            fontSize = "18px";
                            break;
                        case "xlarge":
                            fontSize = "22px";
                            break;
                        default: // medium
                            fontSize = "15px";
                    }
                    
                    var buttonWidth = "auto";
                    var textAlign = "inherit";
                    if (attributes.buttonWidth === "half") {
                        buttonWidth = "50%";
                        textAlign = "center";
                    } else if (attributes.buttonWidth === "full") {
                        buttonWidth = "100%";
                        textAlign = "center";
                    }
                    
                    var positionControl = null;
                    if (attributes.buttonWidth !== "full") {
                        positionControl = el(PanelRow, {},
                            el(SelectControl, {
                                label: "버튼 위치",
                                value: attributes.buttonPosition,
                                options: [
                                    { label: "왼쪽", value: "left" },
                                    { label: "가운데", value: "center" },
                                    { label: "오른쪽", value: "right" }
                                ],
                                onChange: onChangePosition
                            })
                        );
                    }
                    
                    var containerAlignment = attributes.buttonPosition;
                    
                    if (attributes.buttonWidth === "full") {
                        containerAlignment = "center";
                    }
                    
                    var animationClass = "presslearn-button presslearn-button-animation-" + attributes.buttonAnimation;
                    if (infiniteAnimationEnabled === "yes" && attributes.buttonAnimation !== "none") {
                        animationClass += " presslearn-button-animation-infinite";
                    }
                    
                    return [
                        el(InspectorControls, { key: "controls" },
                            el(PanelBody, { title: "버튼 설정", initialOpen: true },
                                el(PanelRow, {},
                                    el(TextControl, {
                                        label: "버튼 텍스트",
                                        value: attributes.buttonText,
                                        onChange: onChangeText
                                    })
                                ),
                                el(PanelRow, {},
                                    el(TextControl, {
                                        label: "버튼 URL",
                                        value: attributes.buttonUrl,
                                        onChange: onChangeUrl
                                    })
                                ),
                                el(PanelRow, {},
                                    el(components.ToggleControl, {
                                        label: "새 탭에서 열기",
                                        checked: attributes.openInNewTab,
                                        onChange: onChangeOpenInNewTab
                                    })
                                ),
                                el(PanelRow, {},
                                    el("div", { className: "components-base-control" },
                                        el("label", { className: "components-base-control__label" }, "버튼 배경 색상"),
                                        el(ColorPalette, {
                                            value: attributes.buttonColor,
                                            onChange: onChangeColor,
                                            clearable: false
                                        })
                                    )
                                ),
                                el(PanelRow, {},
                                    el("div", { className: "components-base-control" },
                                        el("label", { className: "components-base-control__label" }, "버튼 글씨 색상"),
                                        el(ColorPalette, {
                                            value: attributes.buttonTextColor,
                                            onChange: onChangeTextColor,
                                            clearable: false
                                        })
                                    )
                                ),
                                el(PanelRow, {},
                                    el("div", { className: "components-base-control" },
                                        el("label", { className: "components-base-control__label" }, "마우스 오버시 배경 색상"),
                                        el(ColorPalette, {
                                            value: attributes.buttonHoverColor,
                                            onChange: onChangeHoverColor,
                                            clearable: true
                                        })
                                    )
                                ),
                                el(PanelRow, {},
                                    el(SelectControl, {
                                        label: "버튼 가로 크기",
                                        value: attributes.buttonWidth,
                                        options: [
                                            { label: "기본", value: "default" },
                                            { label: "절반", value: "half" },
                                            { label: "꽉찬", value: "full" }
                                        ],
                                        onChange: onChangeWidth
                                    })
                                ),
                                positionControl,
                                el(PanelRow, {},
                                    el(SelectControl, {
                                        label: "버튼 크기",
                                        value: attributes.buttonSize,
                                        options: [
                                            { label: "작게", value: "small" },
                                            { label: "중간", value: "medium" },
                                            { label: "크게", value: "large" },
                                            { label: "매우 크게", value: "xlarge" }
                                        ],
                                        onChange: onChangeSize
                                    })
                                ),
                                el(PanelRow, {},
                                    el(SelectControl, {
                                        label: "애니메이션 효과",
                                        value: attributes.buttonAnimation,
                                        options: [
                                            { label: "없음", value: "none" },
                                            { label: "펄스", value: "pulse" },
                                            { label: "줌", value: "zoom" },
                                            { label: "페이드", value: "fade" },
                                            { label: "떨림", value: "shake" }
                                        ],
                                        onChange: onChangeAnimation
                                    })
                                ),
                                el(PanelRow, {},
                                    el("div", { className: "components-base-control", style: { width: "100%" } },
                                        el("div", { style: { display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "8px" } },
                                            el("label", { 
                                                className: "components-base-control__label", 
                                                style: { margin: 0, fontWeight: "regular",fontSize: "11px", flex: "0 0 80%" } 
                                            }, "모서리 둥글기"),
                                            el("span", { 
                                                style: { 
                                                    flex: "0 0 20%", 
                                                    textAlign: "right", 
                                                    fontWeight: "regular",
                                                    color: "#8b95a1"
                                                } 
                                            }, attributes.borderRadius + "px")
                                        ),
                                        el(components.RangeControl, {
                                            value: attributes.borderRadius,
                                            onChange: onChangeBorderRadius,
                                            min: 0,
                                            max: 50,
                                            step: 1,
                                            withInputField: false,
                                            style: { marginTop: "4px" }
                                        })
                                    )
                                )
                            ),
                            el(PanelBody, { title: "프리셋", initialOpen: false },
                                savedPreset ? el(PanelRow, {},
                                    el("div", { style: { width: "100%" } },
                                        el("p", { style: { marginTop: 0, marginBottom: "10px", color: "#666" } }, "프리셋이 적용된 버튼입니다.")
                                    )
                                ) : null,
                                el(PanelRow, {},
                                    el(components.ButtonGroup, { style: { display: "flex" , width: "100%" , justifyContent: "space-between" , flexDirection: "column" , gap: "10px" , boxSizing: "border-box" } },
                                        el(components.Button, {
                                            isPrimary: true,
                                            onClick: savePreset,
                                            style: { flex: 1 , boxSizing: "border-box" , outline: "none" , boxShadow: "none" }
                                        }, "현재 프리셋 저장"),
                                        savedPreset ? el(components.Button, {
                                            isSecondary: true,
                                            onClick: deletePreset,
                                            style: { flex: 1 , boxSizing: "border-box" , outline: "none" , boxShadow: "none" , border: "1px solid #e1e4e8"}
                                        }, "프리셋 삭제") : null
                                    )
                                )
                            )
                        ),
                        el("div", { className: "presslearn-button-block-editor" },
                            el("div", { 
                                className: "presslearn-button-container",
                                style: { textAlign: containerAlignment }
                            },
                                el("a", {
                                    className: animationClass,
                                    href: "#",
                                    style: {
                                        display: "inline-block",
                                        padding: buttonPadding,
                                        backgroundColor: attributes.buttonColor,
                                        color: attributes.buttonTextColor,
                                        textDecoration: "none",
                                        borderRadius: attributes.borderRadius + "px",
                                        fontWeight: "bold",
                                        fontSize: fontSize,
                                        width: buttonWidth,
                                        textAlign: textAlign,
                                        boxSizing: "border-box",
                                        transition: "all 0.3s ease-in-out"
                                    },
                                    "data-hover-color": attributes.buttonHoverColor || attributes.buttonColor
                                }, attributes.buttonText || "버튼 텍스트")
                            )
                        )
                    ];
                },
                save: function(props) {
                    var attributes = props.attributes;
                    
                    var buttonPadding;
                    switch(attributes.buttonSize) {
                        case "small":
                            buttonPadding = "8px 16px";
                            break;
                        case "large":
                            buttonPadding = "16px 32px";
                            break;
                        case "xlarge":
                            buttonPadding = "20px 40px";
                            break;
                        default: // medium
                            buttonPadding = "12px 24px";
                    }
                    
                    var fontSize;
                    switch(attributes.buttonSize) {
                        case "small":
                            fontSize = "13px";
                            break;
                        case "large":
                            fontSize = "18px";
                            break;
                        case "xlarge":
                            fontSize = "22px";
                            break;
                        default: // medium
                            fontSize = "15px";
                    }
                    
                    var buttonWidth = "auto";
                    var textAlign = "inherit";
                    if (attributes.buttonWidth === "half") {
                        buttonWidth = "50%";
                        textAlign = "center";
                    } else if (attributes.buttonWidth === "full") {
                        buttonWidth = "100%";
                        textAlign = "center";
                    }
                    
                    var containerAlignment = attributes.buttonPosition;
                    
                    if (attributes.buttonWidth === "full") {
                        containerAlignment = "center";
                    }
                    
                    var animationClass = "presslearn-button presslearn-button-animation-" + attributes.buttonAnimation;
                    if (infiniteAnimationEnabled === "yes" && attributes.buttonAnimation !== "none") {
                        animationClass += " presslearn-button-animation-infinite";
                    }
                    
                    return el("div", { 
                        className: "presslearn-button-container",
                        style: { textAlign: containerAlignment, margin: "20px 0" }
                    },
                        el("a", {
                            className: animationClass,
                            href: attributes.buttonUrl || "#",
                            ...(attributes.openInNewTab ? { target: "_blank", rel: "noopener noreferrer" } : {}),
                            style: {
                                display: "inline-block",
                                padding: buttonPadding,
                                backgroundColor: attributes.buttonColor,
                                color: attributes.buttonTextColor,
                                textDecoration: "none",
                                borderRadius: attributes.borderRadius + "px",
                                fontWeight: "bold",
                                fontSize: fontSize,
                                width: buttonWidth,
                                textAlign: textAlign,
                                boxSizing: "border-box",
                                transition: "all 0.3s ease-in-out"
                            },
                            "data-hover-color": attributes.buttonHoverColor || attributes.buttonColor
                        }, attributes.buttonText || "버튼 텍스트")
                    );
                }
            });
        })(
            window.wp.blocks,
            window.wp.element,
            window.wp.components,
            window.wp.blockEditor
        );
    ');
    
    register_block_type('presslearn/quick-button', array(
        'editor_script' => 'presslearn-quick-button-block',
        'render_callback' => 'presslearn_render_quick_button',
        'attributes' => array(
            'buttonText' => array(
                'type' => 'string',
                'default' => '버튼 텍스트'
            ),
            'buttonUrl' => array(
                'type' => 'string',
                'default' => '#'
            ),
            'buttonColor' => array(
                'type' => 'string',
                'default' => '#2196F3'
            ),
            'buttonTextColor' => array(
                'type' => 'string',
                'default' => '#ffffff'
            ),
            'buttonHoverColor' => array(
                'type' => 'string',
                'default' => ''
            ),
            'buttonPosition' => array(
                'type' => 'string',
                'default' => 'center'
            ),
            'buttonSize' => array(
                'type' => 'string',
                'default' => 'medium'
            ),
            'buttonWidth' => array(
                'type' => 'string',
                'default' => 'default'
            ),
            'openInNewTab' => array(
                'type' => 'boolean',
                'default' => false
            ),
            'buttonAnimation' => array(
                'type' => 'string',
                'default' => 'none'
            ),
            'infiniteAnimation' => array(
                'type' => 'string',
                'default' => 'yes'
            ),
            'borderRadius' => array(
                'type' => 'number',
                'default' => 4
            )
        )
    ));
    
    add_filter('block_categories_all', function($categories) {
        return array_merge(
            $categories,
            array(
                array(
                    'slug' => 'presslearn',
                    'title' => 'PressLearn',
                    'icon'  => null,
                ),
            )
        );
    });
}
add_action('init', 'presslearn_register_quick_button_block');

function presslearn_render_quick_button($attributes, $content) {
    if (!empty($content)) {
        return $content;
    }
    
    
    if (!isset($attributes['borderRadius'])) {
        $attributes['borderRadius'] = 4;
    }
    
    $attributes = wp_parse_args($attributes, array(
        'buttonText' => '버튼 텍스트',
        'buttonUrl' => '#',
        'buttonColor' => '#2196F3',
        'buttonTextColor' => '#ffffff',
        'buttonHoverColor' => '',
        'buttonPosition' => 'center',
        'buttonSize' => 'medium',
        'buttonWidth' => 'default',
        'openInNewTab' => false,
        'buttonAnimation' => 'none',
        'infiniteAnimation' => 'yes',
        'borderRadius' => 4
    ));
    
    
    $button_padding = '12px 24px';
    switch ($attributes['buttonSize']) {
        case 'small':
            $button_padding = '8px 16px';
            break;
        case 'large':
            $button_padding = '16px 32px';
            break;
        case 'xlarge':
            $button_padding = '20px 40px';
            break;
    }
    
    $font_size = '15px';
    switch ($attributes['buttonSize']) {
        case 'small':
            $font_size = '13px';
            break;
        case 'large':
            $font_size = '18px';
            break;
        case 'xlarge':
            $font_size = '22px';
            break;
    }
    
    $button_width = 'auto';
    $text_align = 'inherit';
    if ($attributes['buttonWidth'] === 'half') {
        $button_width = '50%';
        $text_align = 'center';
    } else if ($attributes['buttonWidth'] === 'full') {
        $button_width = '100%';
        $text_align = 'center';
    }
    
    $container_alignment = $attributes['buttonPosition'];
    if ($attributes['buttonWidth'] === 'full') {
        $container_alignment = 'center';
    }
    
    $animation_class = 'presslearn-button presslearn-button-animation-' . $attributes['buttonAnimation'];
    $button_transition_enabled = get_option('presslearn_button_transition_enabled', 'no');
    if ($button_transition_enabled === 'yes' && $attributes['buttonAnimation'] !== 'none') {
        $animation_class .= ' presslearn-button-animation-infinite';
    }
    
    $target_attr = $attributes['openInNewTab'] ? ' target="_blank"' : '';
    $rel_attr = $attributes['openInNewTab'] ? ' rel="noopener noreferrer"' : '';
    
    $output = '<div class="presslearn-button-container" style="text-align:' . esc_attr($container_alignment) . ';margin:20px 0">';
    $output .= '<a class="' . esc_attr($animation_class) . '" href="' . esc_url($attributes['buttonUrl']) . '"' . $target_attr . $rel_attr;
    $output .= ' style="display:inline-block;padding:' . esc_attr($button_padding) . ';background-color:' . esc_attr($attributes['buttonColor']) . ';';
    $output .= 'color:' . esc_attr($attributes['buttonTextColor']) . ';text-decoration:none;border-radius:' . esc_attr($attributes['borderRadius']) . 'px;font-weight:bold;';
    $output .= 'font-size:' . esc_attr($font_size) . ';width:' . esc_attr($button_width) . ';text-align:' . esc_attr($text_align) . ';';
    $output .= 'box-sizing:border-box;transition:all 0.3s ease-in-out" data-hover-color="' . esc_attr($attributes['buttonHoverColor'] ?: $attributes['buttonColor']) . '">';
    $output .= esc_html($attributes['buttonText'] ?: '버튼 텍스트') . '</a>';
    $output .= '</div>';
    
    return $output;
}

function presslearn_quick_button_admin_styles() {
    if (!presslearn_is_plugin_active_for_buttons()) {
        return;
    }
    
    $quick_button_enabled = get_option('presslearn_quick_button_enabled', 'no');
    if ($quick_button_enabled !== 'yes') {
        return;
    }
    
    wp_register_style('presslearn-button-admin-styles', false);
    wp_enqueue_style('presslearn-button-admin-styles');
    
    $admin_styles = '
        .presslearn-button-block-editor {
            padding: 20px;
            background: #f5f5f5;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        
        .editor-styles-wrapper .presslearn-button:focus,
        .editor-styles-wrapper .presslearn-button:hover {
            color: #fff !important;
            opacity: 0.9;
        }
        
        .block-editor-block-inspector .components-base-control .components-text-control__input,
        .block-editor-block-inspector .components-base-control .components-select-control__input {
            width: 100% !important;
            box-sizing: border-box !important;
        }
        
        .components-panel__body .components-base-control {
            width: 100%;
        }
        
        .presslearn-button-animation-pulse {
            animation: plButtonPulse 2s ease-in-out;
        }
        .presslearn-button-animation-zoom {
            animation: plButtonZoom 2s ease-in-out;
        }
        .presslearn-button-animation-fade {
            animation: plButtonFade 2s ease-in-out;
        }
        .presslearn-button-animation-shake {
            animation: plButtonShake 2s ease-in-out;
        }
        
        .presslearn-button-animation-infinite {
            animation-iteration-count: infinite !important;
        }
        
        @keyframes plButtonPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes plButtonZoom {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        @keyframes plButtonFade {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        @keyframes plButtonShake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
    ';
    
    wp_add_inline_style('presslearn-button-admin-styles', $admin_styles);
}
add_action('admin_enqueue_scripts', 'presslearn_quick_button_admin_styles');

function presslearn_quick_button_frontend_styles() {
    if (!presslearn_is_plugin_active_for_buttons()) {
        return;
    }
    
    $quick_button_enabled = get_option('presslearn_quick_button_enabled', 'no');
    if ($quick_button_enabled !== 'yes') {
        return;
    }
    
    wp_register_style('presslearn-button-frontend-styles', false);
    wp_enqueue_style('presslearn-button-frontend-styles');
    
    $frontend_styles = '
        .presslearn-button-animation-pulse {
            animation: plButtonPulse 2s ease-in-out;
        }
        .presslearn-button-animation-zoom {
            animation: plButtonZoom 2s ease-in-out;
        }
        .presslearn-button-animation-fade {
            animation: plButtonFade 2s ease-in-out;
        }
        .presslearn-button-animation-shake {
            animation: plButtonShake 2s ease-in-out;
        }
        
        .presslearn-button-animation-infinite {
            animation-iteration-count: infinite !important;
        }
        
        @keyframes plButtonPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes plButtonZoom {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        @keyframes plButtonFade {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        @keyframes plButtonShake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .presslearn-button:hover {
            opacity: 0.9;
        }
    ';
    
    wp_add_inline_style('presslearn-button-frontend-styles', $frontend_styles);
}
add_action('wp_enqueue_scripts', 'presslearn_quick_button_frontend_styles');

function presslearn_quick_button_frontend_scripts() {
    if (!presslearn_is_plugin_active_for_buttons()) {
        return;
    }
    
    $quick_button_enabled = get_option('presslearn_quick_button_enabled', 'no');
    if ($quick_button_enabled !== 'yes') {
        return;
    }
    
    wp_register_script('presslearn-button-frontend-script', false, array('jquery'), time(), true);
    wp_enqueue_script('presslearn-button-frontend-script');
    
    $script = '
    jQuery(document).ready(function($) {
        $(document).on("mouseenter", ".presslearn-button", function() {
            var $this = $(this);
            var hoverColor = $this.attr("data-hover-color");
            if (hoverColor) {
                $this.data("original-color", $this.css("background-color"));
                $this.css("background-color", hoverColor);
            }
        });
        
        $(document).on("mouseleave", ".presslearn-button", function() {
            var $this = $(this);
            var originalColor = $this.data("original-color");
            if (originalColor) {
                $this.css("background-color", originalColor);
            }
        });
    });
    ';
    
    wp_add_inline_script('presslearn-button-frontend-script', $script);
}
add_action('wp_enqueue_scripts', 'presslearn_quick_button_frontend_scripts');

function presslearn_save_button_preset() {
    check_ajax_referer('presslearn_button_preset', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('권한이 없습니다.');
    }
    
    $preset_data = isset($_POST['preset']) ? stripslashes($_POST['preset']) : '';
    
    if (empty($preset_data)) {
        wp_send_json_error('프리셋 데이터가 없습니다.');
    }
    
    $preset = json_decode($preset_data, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error('잘못된 프리셋 데이터입니다.');
    }
    
    update_option('presslearn_button_preset', $preset);
    wp_send_json_success('프리셋이 저장되었습니다.');
}
add_action('wp_ajax_presslearn_save_button_preset', 'presslearn_save_button_preset');

function presslearn_delete_button_preset() {
    check_ajax_referer('presslearn_button_preset', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('권한이 없습니다.');
    }
    
    delete_option('presslearn_button_preset');
    wp_send_json_success('프리셋이 삭제되었습니다.');
}
add_action('wp_ajax_presslearn_delete_button_preset', 'presslearn_delete_button_preset');
