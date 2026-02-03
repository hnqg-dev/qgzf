#!/usr/bin/env php
<?php
/**
 * 独立PHP代码混淆器 - 无需任何依赖
 * 用法: php standalone-obfuscator.php [选项] [文件/目录]
 */

class StandalonePHPObfuscator
{
    private $config = [
        'rename_variables' => true,      // 重命名变量
        'rename_functions' => true,      // 重命名函数
        'rename_classes'   => true,      // 重命名类名
        'remove_comments'  => true,      // 删除注释
        'remove_whitespace' => true,     // 删除多余空白
        'obfuscate_strings' => false,    // 混淆字符串（基础）
        'keep_keywords'    => true,      // 保留PHP关键字
        'output_dir'       => 'obfuscated_output',
        'exclude_dirs'     => ['vendor', 'runtime', 'public', 'tests'],
        'exclude_files'    => ['*.blade.php', '*.tpl.php', '*.html'],
    ];
    
    private $variable_map = [];
    private $function_map = [];
    private $class_map = [];
    private $counter = 0;
    
    public function run($argv)
    {
        echo "========================================\n";
        echo "    独立PHP混淆器 v1.0\n";
        echo "========================================\n\n";
        
        if (count($argv) < 2) {
            $this->showHelp();
            return;
        }
        
        $command = $argv[1];
        
        switch ($command) {
            case 'file':
                if (count($argv) < 4) {
                    echo "错误: 用法: php standalone-obfuscator.php file 输入文件 输出文件\n";
                    return;
                }
                $this->obfuscateFile($argv[2], $argv[3]);
                break;
                
            case 'dir':
                if (count($argv) < 3) {
                    echo "错误: 用法: php standalone-obfuscator.php dir 目录路径\n";
                    return;
                }
                $outputDir = isset($argv[3]) ? $argv[3] : $this->config['output_dir'];
                $this->obfuscateDirectory($argv[2], $outputDir);
                break;
                
            case 'config':
                $this->showConfig();
                break;
                
            case 'test':
                $this->runTest();
                break;
                
            default:
                if (file_exists($command)) {
                    $outputFile = isset($argv[2]) ? $argv[2] : $command . '.obf';
                    $this->obfuscateFile($command, $outputFile);
                } else {
                    echo "错误: 未知命令 '$command'\n";
                    $this->showHelp();
                }
        }
    }
    
    private function obfuscateFile($inputFile, $outputFile)
    {
        if (!file_exists($inputFile)) {
            echo "错误: 输入文件不存在: $inputFile\n";
            return false;
        }
        
        echo "正在处理: $inputFile\n";
        
        $code = file_get_contents($inputFile);
        $obfuscatedCode = $this->obfuscateCode($code);
        
        // 确保输出目录存在
        $outputDir = dirname($outputFile);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        
        file_put_contents($outputFile, $obfuscatedCode);
        
        $originalSize = strlen($code);
        $obfuscatedSize = strlen($obfuscatedCode);
        $ratio = ($originalSize > 0) ? round(($obfuscatedSize / $originalSize) * 100, 2) : 0;
        
        echo "✓ 完成: $outputFile\n";
        echo "  大小: {$originalSize} → {$obfuscatedSize} 字节 ({$ratio}%)\n";
        
        return true;
    }
    
    private function obfuscateDirectory($inputDir, $outputDir)
    {
        if (!is_dir($inputDir)) {
            echo "错误: 输入目录不存在: $inputDir\n";
            return false;
        }
        
        echo "开始处理目录: $inputDir\n";
        echo "输出目录: $outputDir\n\n";
        
        // 创建输出目录
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        
        $files = $this->scanDirectory($inputDir);
        $total = count($files);
        $success = 0;
        $failed = 0;
        
        foreach ($files as $i => $file) {
            $relativePath = substr($file, strlen($inputDir) + 1);
            $outputFile = $outputDir . '/' . $relativePath;
            
            echo "[" . ($i + 1) . "/$total] $relativePath\n";
            
            if ($this->obfuscateFile($file, $outputFile)) {
                $success++;
            } else {
                $failed++;
            }
        }
        
        echo "\n========================================\n";
        echo "处理完成!\n";
        echo "成功: $success 文件\n";
        echo "失败: $failed 文件\n";
        echo "输出目录: $outputDir\n";
        
        // 复制非PHP文件
        $this->copyNonPhpFiles($inputDir, $outputDir);
        
        return true;
    }
    
    private function obfuscateCode($code)
    {
        // 重置映射
        $this->variable_map = [];
        $this->function_map = [];
        $this->class_map = [];
        $this->counter = 0;
        
        // 步骤1: 提取并重命名类名
        if ($this->config['rename_classes']) {
            $code = $this->renameClasses($code);
        }
        
        // 步骤2: 提取并重命名函数名
        if ($this->config['rename_functions']) {
            $code = $this->renameFunctions($code);
        }
        
        // 步骤3: 提取并重命名变量名
        if ($this->config['rename_variables']) {
            $code = $this->renameVariables($code);
        }
        
        // 步骤4: 删除注释
        if ($this->config['remove_comments']) {
            $code = $this->removeComments($code);
        }
        
        // 步骤5: 删除多余空白
        if ($this->config['remove_whitespace']) {
            $code = $this->removeWhitespace($code);
        }
        
        // 步骤6: 基础字符串混淆
        if ($this->config['obfuscate_strings']) {
            $code = $this->obfuscateStrings($code);
        }
        
        return $code;
    }
    
    private function renameClasses($code)
    {
        // 匹配类定义
        preg_match_all('/(?:^|\s)(class|interface|trait)\s+(\w+)/', $code, $matches);
        
        foreach ($matches[2] as $originalName) {
            // 跳过PHP内置类和关键词
            if (in_array(strtolower($originalName), ['self', 'parent', 'static', 'array', 'string', 'int', 'float', 'bool', 'object', 'mixed', 'void', 'null', 'true', 'false'])) {
                continue;
            }
            
            if (!isset($this->class_map[$originalName])) {
                $newName = 'C' . $this->counter++;
                $this->class_map[$originalName] = $newName;
            }
        }
        
        // 替换类名
        foreach ($this->class_map as $old => $new) {
            $code = preg_replace('/(^|\s)(class|interface|trait|new|extends|implements)\s+' . $old . '(\s|\(|;)/', '$1$2 ' . $new . '$3', $code);
            $code = preg_replace('/(\W)' . $old . '::/', '$1' . $new . '::', $code);
        }
        
        return $code;
    }
    
    private function renameFunctions($code)
    {
        // 匹配函数定义
        preg_match_all('/function\s+(\w+)\s*\(/', $code, $matches);
        
        foreach ($matches[1] as $originalName) {
            // 跳过魔术方法和PHP内置函数
            if (substr($originalName, 0, 2) == '__' || 
                in_array(strtolower($originalName), ['echo', 'print', 'isset', 'empty', 'unset', 'include', 'require', 'include_once', 'require_once'])) {
                continue;
            }
            
            if (!isset($this->function_map[$originalName])) {
                $newName = 'f' . $this->counter++;
                $this->function_map[$originalName] = $newName;
            }
        }
        
        // 替换函数名（在调用时）
        foreach ($this->function_map as $old => $new) {
            $code = preg_replace('/([^>\w])' . $old . '\s*\(/', '$1' . $new . '(', $code);
        }
        
        return $code;
    }
    
    private function renameVariables($code)
    {
        // 匹配变量名（简单的匹配，可能不完美）
        preg_match_all('/\$(\w+)/', $code, $matches);
        
        foreach ($matches[1] as $originalName) {
            // 跳过超全局变量
            if (in_array($originalName, ['_GET', '_POST', '_REQUEST', '_SESSION', '_COOKIE', '_SERVER', '_ENV', '_FILES', 'GLOBALS', 'this'])) {
                continue;
            }
            
            if (!isset($this->variable_map[$originalName])) {
                $newName = 'v' . $this->counter++;
                $this->variable_map[$originalName] = $newName;
            }
        }
        
        // 替换变量名
        foreach ($this->variable_map as $old => $new) {
            $code = str_replace('$' . $old, '$' . $new, $code);
        }
        
        return $code;
    }
    
    private function removeComments($code)
    {
        // 删除多行注释
        $code = preg_replace('/\/\*.*?\*\//s', '', $code);
        
        // 删除单行注释
        $code = preg_replace('/\/\/.*$/m', '', $code);
        
        // 删除井号注释
        $code = preg_replace('/#.*$/m', '', $code);
        
        return $code;
    }
    
    private function removeWhitespace($code)
    {
        // 替换多个空格为单个空格
        $code = preg_replace('/\s+/', ' ', $code);
        
        // 删除语句周围的空格
        $code = preg_replace('/\s*([=+\-*\/%&|^~!<>{}();,:\[\]])\s*/', '$1', $code);
        
        // 删除空行
        $code = preg_replace('/\n\s*\n/', "\n", $code);
        
        return trim($code);
    }
    
    private function obfuscateStrings($code)
    {
        // 简单字符串编码（base64）
        preg_match_all('/["\']([^"\']+)["\']/', $code, $matches);
        
        foreach ($matches[0] as $i => $fullMatch) {
            $stringContent = $matches[1][$i];
            
            // 跳过太短的字符串
            if (strlen($stringContent) < 3) {
                continue;
            }
            
            // 跳过可能包含变量或特殊字符的字符串
            if (strpos($stringContent, '$') !== false || 
                strpos($stringContent, '\\') !== false) {
                continue;
            }
            
            $encoded = base64_encode($stringContent);
            $replacement = 'base64_decode("' . $encoded . '")';
            
            $code = str_replace($fullMatch, $replacement, $code);
        }
        
        return $code;
    }
    
    private function scanDirectory($dir)
    {
        $files = [];
        
        if (!is_dir($dir)) {
            return $files;
        }
        
        $items = scandir($dir);
        
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            
            $fullPath = $dir . '/' . $item;
            
            // 检查是否在排除目录中
            $exclude = false;
            foreach ($this->config['exclude_dirs'] as $excludeDir) {
                if (strpos($fullPath, '/' . $excludeDir . '/') !== false || 
                    basename($fullPath) == $excludeDir) {
                    $exclude = true;
                    break;
                }
            }
            
            if ($exclude) {
                continue;
            }
            
            // 检查是否匹配排除文件模式
            foreach ($this->config['exclude_files'] as $pattern) {
                if (fnmatch($pattern, $item)) {
                    continue 2;
                }
            }
            
            if (is_dir($fullPath)) {
                $subFiles = $this->scanDirectory($fullPath);
                $files = array_merge($files, $subFiles);
            } elseif (is_file($fullPath) && pathinfo($fullPath, PATHINFO_EXTENSION) == 'php') {
                $files[] = $fullPath;
            }
        }
        
        return $files;
    }
    
    private function copyNonPhpFiles($source, $dest)
    {
        if (!is_dir($source)) {
            return;
        }
        
        $items = scandir($source);
        
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            
            $sourcePath = $source . '/' . $item;
            $destPath = $dest . '/' . $item;
            
            if (is_dir($sourcePath)) {
                // 如果是排除目录，跳过
                $exclude = false;
                foreach ($this->config['exclude_dirs'] as $excludeDir) {
                    if (basename($sourcePath) == $excludeDir) {
                        $exclude = true;
                        break;
                    }
                }
                
                if (!$exclude) {
                    if (!is_dir($destPath)) {
                        mkdir($destPath, 0755, true);
                    }
                    $this->copyNonPhpFiles($sourcePath, $destPath);
                }
            } elseif (is_file($sourcePath) && pathinfo($sourcePath, PATHINFO_EXTENSION) != 'php') {
                // 复制非PHP文件
                copy($sourcePath, $destPath);
            }
        }
    }
    
    private function runTest()
    {
        echo "运行测试...\n\n";
        
        $testCode = <<<'PHP'
<?php
/**
 * 测试类
 */
class TestClass {
    private $name;
    public $value = 100;
    
    public function __construct($name) {
        $this->name = $name;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function calculate($a, $b) {
        $sum = $a + $b;
        return $sum * $this->value;
    }
}

// 使用示例
$test = new TestClass("示例");
echo $test->getName();
echo $test->calculate(10, 20);
PHP;

        echo "原始代码:\n";
        echo "--------\n";
        echo $testCode . "\n\n";
        
        $obfuscated = $this->obfuscateCode($testCode);
        
        echo "混淆后代码:\n";
        echo "--------\n";
        echo $obfuscated . "\n\n";
        
        echo "映射表:\n";
        echo "------\n";
        echo "类映射: " . json_encode($this->class_map) . "\n";
        echo "函数映射: " . json_encode($this->function_map) . "\n";
        echo "变量映射: " . json_encode($this->variable_map) . "\n";
    }
    
    private function showConfig()
    {
        echo "当前配置:\n";
        echo "--------\n";
        
        foreach ($this->config as $key => $value) {
            if (is_array($value)) {
                echo "$key: " . implode(', ', $value) . "\n";
            } else {
                echo "$key: " . ($value ? '是' : '否') . "\n";
            }
        }
    }
    
    private function showHelp()
    {
        echo "用法:\n";
        echo "  php standalone-obfuscator.php <命令> [参数]\n\n";
        echo "命令:\n";
        echo "  file <输入文件> <输出文件>    混淆单个文件\n";
        echo "  dir <目录> [输出目录]         混淆整个目录（默认输出到 obfuscated_output）\n";
        echo "  config                       显示当前配置\n";
        echo "  test                         运行测试\n";
        echo "  <文件路径> [输出文件]         直接混淆文件\n\n";
        echo "示例:\n";
        echo "  php standalone-obfuscator.php file app/Controller.php app/Controller_obf.php\n";
        echo "  php standalone-obfuscator.php dir /wwwroot/project\n";
        echo "  php standalone-obfuscator.php dir /wwwroot/project /wwwroot/obfuscated\n";
        echo "  php standalone-obfuscator.php app/Controller.php\n\n";
        echo "排除目录: " . implode(', ', $this->config['exclude_dirs']) . "\n";
    }
}

// 运行混淆器
if (PHP_SAPI === 'cli') {
    $obfuscator = new StandalonePHPObfuscator();
    $obfuscator->run($argv);
} else {
    echo "此工具只能在命令行中使用。\n";
    echo "Usage: php standalone-obfuscator.php [command]\n";
}