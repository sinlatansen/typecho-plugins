<?php
// rebuild.php

// 包括Typecho初始化文件，以便您可以使用框架和插件的功能。
// 注意：请将以下路径替换为您实际Typecho安装的路径。
require_once '/usr/local/lighthouse/softwares/typecho/index.php';

// 调用您的插件中的重建索引方法
try {
    ExSearch_Plugin::save();
    echo "Rebuild successfully!";
} catch (Exception $e) {
    // 如果出现异常，您可以在这里处理
    echo "An error occurred: " . $e->getMessage();
}
?>