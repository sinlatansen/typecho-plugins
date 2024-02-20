<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Sakana widget for Web（网页小组件版本的石蒜模拟器）Typecho适配插件
 * 本项目基于dsrkafuu的<a href="https://github.com/dsrkafuu/sakana-widget">sakana-widget</a>二次开发
 * 
 * @package SakanaTypecho
 * @author 社会易姐QwQ
 * @version 1.0.0
 * @link https://shakaianee.top
 */
class SakanaTypecho_Plugin implements Typecho_Plugin_Interface
{
    private static $jsUrl_local = '/SakanaTypecho/sakana.min.js';
    private static $jsUrl_jsdelivr = 'https://cdn.jsdelivr.net/npm/sakana-widget@2.3.0/lib/sakana.min.js';
    private static $jsUrl_cloudflare = 'https://cdnjs.cloudflare.com/ajax/libs/sakana-widget/2.3.0/sakana.min.js';
    /**
     * 激活插件方法
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        // 注册Hook函数
        Typecho_Plugin::factory('Widget_Archive')->header = ['SakanaTypecho_Plugin', 'outputHeader'];
        Typecho_Plugin::factory('Widget_Archive')->footer = ['SakanaTypecho_Plugin', 'outputFooter'];
    }

    /**
     * 禁用插件方法
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
    }

    /**
     * 插件配置方法
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $cdn_source = new Typecho_Widget_Helper_Form_Element_Radio(
            'cdn_source',
            [
                'local' => '本地',
                'jsdelivr' => 'JSDelivr',
                'cloudflare' => 'CloudFlare'
            ],
            'local',
            _t('组件CDN源选择')
        );
        $form->addInput($cdn_source);

        $default_character = new Typecho_Widget_Helper_Form_Element_Text(
            'default_character',
            NULL,
            'takina',
            _t('默认显示的角色'),
            _t('takina：井之上泷奈</br>chisato：锦木千束</br>详情参考<a href="https://github.com/dsrkafuu/sakana-widget/blob/main/README.zh.md">sakana-widget项目文档</a>')
        );
        $form->addInput($default_character);

        $display_setting = new Typecho_Widget_Helper_Form_Element_Checkbox(
            'display_setting',
            [
                'auto_fit' => '自动缩放（没事别关）',
                'fit_mobile' => '自适应手机端页面（取消边距并缩放）',
                'auto_trigget' => '默认开启自走模式'
            ],
            ['auto_fit', 'fit_mobile'],
            _t('显示设置')
        );
        $form->addInput($display_setting);

        $widget_pos = new Typecho_Widget_Helper_Form_Element_Radio(
            'widget_pos',
            [
                'left' => '左',
                'right' => '右'
            ],
            'left',
            _t('组件在页面下方的位置')
        );
        $form->addInput($widget_pos);

        $widget_size = new Typecho_Widget_Helper_Form_Element_Text(
            'widget_size',
            NULL,
            '200',
            _t('组件大小'),
            _t('输入符合css标准的值（px单位）')
        );
        $form->addInput($widget_size->addRule('isFloat', _t('请填写正确的尺寸！')));

        $bottom_distance = new Typecho_Widget_Helper_Form_Element_Text(
            'bottom_distance',
            NULL,
            '24',
            _t('底边距'),
            _t('输入符合css标准的值（px单位）')
        );
        $form->addInput($bottom_distance->addRule('isFloat', _t('请填写正确的边距！')));

        $side_distance = new Typecho_Widget_Helper_Form_Element_Text(
            'side_distance',
            NULL,
            '10',
            _t('侧边距'),
            _t('输入符合css标准的值（px单位）')
        );
        $form->addInput($side_distance->addRule('isFloat', _t('请填写正确的边距！')));
    }

    /**
     * 个人用户的配置方法
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * 插件实现函数 输出header
     *
     * @access public
     * @param string $header
     * @param Widget_Archive $archive
     * @return void
     */
    public static function outputHeader(string $header, Widget_Archive $archive)
    {
        $config = Typecho_Widget::widget('Widget_Options')->Plugin('SakanaTypecho');
?>
        <style>
            #sakana-widget {
                position: fixed;
                <?= $config->widget_pos ?>: <?= $config->side_distance ?>px;
                bottom: <?= $config->bottom_distance ?>px;
                height: <?= $config->widget_size ?>px;
                width: <?= $config->widget_size ?>px;
            }

            #sakana-bubble {
                position: fixed;
                bottom: <?= $config->bottom_distance + 215 ?>px;
                padding: 10px;
                background-color: #f0f0f0;
                color: #333;
                font-size: 1em;
                border-radius: 10px;
                /* border: 2px solid #ccc; */
                filter: drop-shadow(0px 4px 8px rgba(0, 0, 0, 0.2));
                opacity: 0;
                /* 初始状态为透明 */
                transform: translateY(10px);
                /* 初始状态向下移动10px */
                transition: opacity 0.5s, transform 0.5s;
                /* 定义过渡效果 */
                /* Initially hidden */
                z-index: 100;
                /* Ensure it's above other content */
            }

            #sakana-bubble.show {
                opacity: 1;
                /* 显示时完全不透明 */
                transform: translateY(0);
                /* 显示时回到原位 */
            }

            #sakana-bubble::after {
                content: '';
                position: fixed;
                bottom: <?= $config->bottom_distance - 40 ?>px;
                left: 60px;
                border-width: 10px;
                border-style: solid;
                border-color: #f0f0f0 transparent transparent transparent;
                /* The first value is the color of the triangle (should match the bubble's background) */
            }

            <?php
            if (@in_array('fit_mobile', $config->display_setting)) {
                // 手机端的分类讨论处理
            ?>@media(max-width: 1024px) {
                #sakana-widget {
                    bottom: 10px;
                    <?= $config->widget_pos ?>: 0px;
                    height: 120px;
                    width: 120px;
                }

                #sakana-bubble {
                    position: fixed;
                    bottom: 150px;
                    padding: 8px;
                    background-color: #f0f0f0;
                    color: #333;
                    font-size: 0.8em;
                    border-radius: 10px;
                    /* border: 2px solid #ccc; */
                    /* box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); */
                    opacity: 0;
                    /* 初始状态为透明 */
                    transform: translateY(10px);
                    /* 初始状态向下移动10px */
                    transition: opacity 0.5s, transform 0.5s;
                    /* 定义过渡效果 */
                    /* Initially hidden */
                    z-index: 100;
                    /* Ensure it's above other content */
                }

                #sakana-bubble.show {
                    opacity: 1;
                    /* 显示时完全不透明 */
                    transform: translateY(0);
                    /* 显示时回到原位 */
                }

                #sakana-bubble::after {
                    content: '';
                    position: fixed;
                    bottom: -18px;
                    left: 60px;
                    border-width: 10px;
                    border-style: solid;
                    border-color: #f0f0f0 transparent transparent transparent;
                    /* The first value is the color of the triangle (should match the bubble's background) */
                }

                <?php } ?>
        </style>
    <?php
    }

    /**
     * 插件实现函数 输出footer
     *
     * @access public
     * @param Widget_Archive $archive
     * @return void
     */
    public static function outputFooter(Widget_Archive $archive)
    {
        $config = Typecho_Widget::widget('Widget_Options')->Plugin('SakanaTypecho');
        // CDN源选择，获取js库的url
        switch ($config->cdn_source) {
            case 'local':
                $jsUrl = Helper::options()->pluginUrl . self::$jsUrl_local;
                break;
            case 'jsdelivr':
                $jsUrl = self::$jsUrl_jsdelivr;
                break;
            case 'cloudflare':
                $jsUrl = self::$jsUrl_cloudflare;
                break;
        }
        // 对话内容数组
        $dialogues = json_encode([
            "Sakana~",
            "I'm a talking fish!",
            "最近倒春寒，各位朋友小心感冒~",
            "网站的所有秘密在那篇置顶文章~",
            "搜索功能很好用噢，不用慢慢找啦~",
            "不...不可以扒拉我~",
            "右下角的蓝色按钮可以返回顶部~"
        ]);
    ?>
        <div id="sakana-widget"></div>
        <div id="sakana-bubble">Dialogue goes here...</div> <!-- Dialogue bubble element -->
        <script src="<?= $jsUrl ?>"></script>
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                new SakanaWidget({
                        character: '<?= $config->default_character ?>',
                        autoFit: <?= @in_array('auto_fit', $config->display_setting) ? 'true' : 'false' ?>
                    })
                    .mount('#sakana-widget')
                <?php if (@in_array('auto_trigget', $config->display_setting)) { ?>
                        .triggetAutoMode()
                <?php } ?>
                const dialogues = <?= $dialogues ?>;
                const bubble = document.getElementById('sakana-bubble');
                let i = 1;

                function showDialogue() {
                    /* 随机 */
                    /* const randomIndex = Math.floor(Math.random() * dialogues.length);
                    bubble.textContent = dialogues[randomIndex]; */
                    bubble.textContent = '🐟 ' + dialogues[i % dialogues.length];
                    i = i + 1;
                    bubble.classList.add('show');

                    /* 一段时间后隐藏气泡，可根据需要调整时间 */
                    setTimeout(() => {
                        bubble.classList.remove('show');
                    }, 5000);
                }

                showDialogue();
                /* debugger; */
                setInterval(showDialogue, 6000);
            });
        </script>
<?php
    }
}
