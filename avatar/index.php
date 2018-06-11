<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
check_user_status();
?>
<!doctype html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Avatar maker</title>
    <link rel="stylesheet" href="css/colorpicker.css"/>
    <link rel="stylesheet" href="css/fancycheckbox.css"/>
    <link rel="stylesheet" href="css/avatarmaker.css"/>
    <script src="js/fancycheckbox.js"></script>
    <script src="js/jquery.js"></script>
    <script src="js/colorpicker.js"></script>
    <script src="js/avatarmaker-core.js"></script>
    <script>
        /*<![CDATA[*/
        $(document).ready(function () {
            $('#colorSelector').ColorPicker({
                color: "#" + $('#bgColor').val(),
                onChange: function (hsb, hex, rgb) {
                    $('#bgColor').val(hex);
                }
            });
            $('#colorSelector1').ColorPicker({
                color: "#" + $('#fColor').val() + "",
                onChange: function (hsb, hex, rgb) {
                    $('#fColor').val(hex);
                    //update();
                }
            });
            $('#colorSelector2').ColorPicker({
                color: "#" + $('#bColor').val() + "",
                onChange: function (hsb, hex, rgb) {
                    $('#bColor').val(hex);
                    //update();
                }
            });
            $('div.colorpicker_color').mouseup(function () {
                update();
            });
            get_vars();
            get_drp(true);
            crir.init();
        });
        /*]]>*/
    </script>
</head>
<body>
<div class="loader is_hidden">Wait while the avatar is saved!</div>
<table class="has-text-centered">
    <tr>
        <td>
            <fieldset>
                <legend>Preview</legend>
                <div class="has-text-centered">
                    <input type="hidden" value="<?php
                    global $CURUSER;
                    echo $CURUSER['username']; ?>" id="user"/>
                    <img id="preview" src="avatar.php?user=<?php
                    global $CURUSER;
                    echo $CURUSER['username']; ?>" width="150" height="190" alt="Avatar"/></div>
            </fieldset>
        </td>
        <td>
            <fieldset style="width:400px;">
                <legend>Avatar body</legend>
                <table class="avy_body has-text-centered">
                    <tr>
                        <td nowrap="nowrap" class="info">Background color</td>
                        <td width="100%"><input type="text" id="bgColor" readonly="readonly" size="25"/>
                            <img id="colorSelector" title="Select color"
                                 src="{$site_config['pic_baseurl']}color_wheel.png" width="16"
                                 height="16" alt="Color Wheel"/></td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap" class="info">Font color</td>
                        <td width="100%"><input type="text" id="fColor" readonly="readonly" size="25"/>
                            <img id="colorSelector1" title="Select color"
                                 src="{$site_config['pic_baseurl']}color_wheel.png" width="16"
                                 height="16" alt="Color Wheel"/></td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap" class="info">Border color</td>
                        <td width="100%"><input type="text" id="bColor" readonly="readonly" size="25"/>
                            <img id="colorSelector2" title="Select color"
                                 src="{$site_config['pic_baseurl']}color_wheel.png" width="16"
                                 height="16" alt="Color Wheel"/></td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap" class="info">Smile pack</td>
                        <td width="100%"><select id="pack" onchange="update();">
                                <option value="1">Default</option>
                                <option value="2">Blacy</option>
                                <option value="3">Popo</option>
                                <option value="4">Buttery</option>
                            </select></td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap" class="info">Smile</td>
                        <td width="100%"><select id="smile" onchange="update();">
                                <option value="225">Random</option>
                                <option value="1">Smile 1</option>
                                <option value="2">Smile 2</option>
                                <option value="3">Smile 3</option>
                                <option value="4">Smile 4</option>
                                <option value="5">Smile 5</option>
                                <option value="6">Smile 6</option>
                                <option value="7">Smile 7</option>
                                <option value="8">Smile 8</option>
                                <option value="9">Smile 9</option>
                                <option value="10">Smile 10</option>
                                <option value="11">Smile 11</option>
                                <option value="12">Smile 12</option>
                                <option value="13">Smile 13</option>
                                <option value="14">Smile 14</option>
                                <option value="15">Smile 15</option>
                                <option value="16">Smile 16</option>
                                <option value="17">Smile 17</option>
                                <option value="18">Smile 18</option>
                                <option value="19">Smile 19</option>
                                <option value="20">Smile 20</option>
                            </select></td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap" class="info">Font</td>
                        <td width="100%"><select id="font" onchange="update();">
                                <option value="1">Font 1</option>
                                <option value="2">Font 2</option>
                                <option value="3">Font 3</option>
                            </select></td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
    <tr>
        <td>
            <fieldset style="width:170px;">
                <legend>Final link</legend>
                <table class="avy_body has-text-centered">
                    <tr>
                        <td nowrap="nowrap" class="info"><input style="width:150px;" type="text" onclick="select();"
                                                                value="<?php
                                                                global $CURUSER, $site_config;
                                                                echo $site_config['baseurl']; ?>/avatar/settings/<?php
                                                                echo strtolower($CURUSER['username']); ?>.png"
                                                                readonly="readonly"/>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
        <td>
            <fieldset style="width:400px;">
                <legend>Settings</legend>
                <table class="avy_body has-text-centered">
                    <tr>
                        <td nowrap="nowrap" class="info">Line 1</td>
                        <td class="has-text-centered w-100">
                            <select id="drp1" onchange="get_drp();change_label('line1',this.value);update();">
                            </select>
                            <input type="text" id="line1" onchange="update();"/>
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap" class="info">Line 2</td>
                        <td class="has-text-centered w-100">
                            <select id="drp2" onchange="get_drp();change_label('line2',this.value);update();">
                            </select>
                            <input type="text" id="line2" onchange="update();"/>
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap" class="info">Line 3</td>
                        <td class="has-text-centered w-100">
                            <select id="drp3" onchange="get_drp();change_label('line3',this.value);update();">
                            </select>
                            <input type="text" id="line3" onchange="update();"/>
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap" class="info">Show<br>
                            Username
                        </td>
                        <td width="100%"><label for="suser">
                                <input type="checkbox" id="suser" onchange="update();" class="crirHiddenJS"/>
                                Tick this if you want your username on the avatar!</label></td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
</body>
</html>
