<?php

$namespace = "cooldogedev\\BuildBattle\\utility\\message";
$className = "KnownMessages";
$specialKey = "";
$path = "../resources/languages";
$file = "en-US.yml";
$indent = 5;

function parse(array $translations, string &$code, string &$indentation, int &$current, int $last, ?string $key = null): void {
    foreach ($translations as $topic => $messages) {

        if ($current > 1) {
            $code .= PHP_EOL;
        }

        $constant = str_replace("-", "_", strtoupper($topic));
        $code .= $indentation . "public const TOPIC_$constant = \"$topic\";";

        foreach ($messages as $key => $value) {
            $message = str_replace("-", "_", strtoupper($key));
            $code .= PHP_EOL . $indentation . "public const " . $constant . "_" . $message . " = \"$key\";";
        }

        if ($last > $current) {
            $code .= PHP_EOL;
        }


        $current++;
    }
}

function main(): void
{
    global $namespace, $className, $specialKey, $path, $file, $indent;

    $outputPath = realpath("../src/" . str_replace("\\", DIRECTORY_SEPARATOR, $namespace));

    $translations = yaml_parse_file(realpath($path) . DIRECTORY_SEPARATOR . $file);

    if (!is_array($translations)) {
        throw new Exception("Could not parse YAML file");
    }

    if ($specialKey !== "") {
        $translations = $translations[$specialKey] ?? [];
    }

    $template = "<?php

declare(strict_types=1);

namespace $namespace;

final class $className
{
__CODE__
}
";

    $code = "";
    $indentation = str_repeat(" ", $indent);

    $last = count($translations);
    $current = 1;

    parse($translations, $code, $indentation, $current, $last);

    file_put_contents($outputPath . DIRECTORY_SEPARATOR . $className . ".php", str_replace("__CODE__", $code, $template));
}

main();
