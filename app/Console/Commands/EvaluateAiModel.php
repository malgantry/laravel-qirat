<?php

namespace App\Console\Commands;

use App\Services\AiClient;
use App\Services\Ai\Metrics;
use Illuminate\Console\Command;

class EvaluateAiModel extends Command
{
    /** @var string */
    protected $signature = 'ai:evaluate 
        {--file= : مسار ملف CSV}
        {--features= : أسماء الأعمدة (مع رأس) أو فهارس بدون رأس، مفصولة بفواصل}
        {--label= : اسم عمود الوسم الحقيقي أو فهرس بدون رأس}
        {--sep=, : فاصل الأعمدة}
        {--no-header : الملف بلا رأس أعمدة}
        {--out= : حفظ نتائج التقييم في ملف JSON}
        {--dummy : استخدام مُتنبئ محلي بسيط بدلاً من خدمة الـAI}
        {--classes= : عدد الفئات للمُتنبئ المحلي (إن لزم)}';

    /** @var string */
    protected $description = 'تقييم دقة نموذج الذكاء على ملف CSV (دقة، F1، مصفوفة الالتباس).';

    public function __construct(private readonly AiClient $aiClient)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $file = $this->option('file');
        $featuresOpt = $this->option('features');
        $labelOpt = $this->option('label');
        $sep = $this->option('sep') ?? ',';
        $noHeader = (bool) $this->option('no-header');
        $outPath = $this->option('out');
        $useDummy = (bool) $this->option('dummy');
        $classesOpt = $this->option('classes');

        if (!$file || !$featuresOpt || !$labelOpt) {
            $this->error('يجب تحديد --file و --features و --label');
            return self::FAILURE;
        }
        if (!is_file($file)) {
            $this->error('الملف غير موجود: ' . $file);
            return self::FAILURE;
        }

        $featuresCols = array_map('trim', explode(',', $featuresOpt));
        $labelCol = trim($labelOpt);

        // Optional: verify input_size from metadata and read output classes
        $metadata = null;
        $outputClasses = null;
        try {
            $metadata = $this->aiClient->metadata();
            $inputSize = $metadata['input_size'] ?? ($metadata['input_shape'][0] ?? null);
            if (is_int($inputSize) && $inputSize > 0 && $inputSize !== count($featuresCols)) {
                $this->warn("تحذير: عدد ميزات الإدخال (" . count($featuresCols) . ") لا يطابق input_size في metadata ($inputSize)");
            }
            // Infer classes from labels length or output_shape
            if (isset($metadata['labels']) && is_array($metadata['labels'])) {
                $outputClasses = count($metadata['labels']);
            } elseif (isset($metadata['output_shape']) && is_array($metadata['output_shape']) && isset($metadata['output_shape'][0])) {
                $outputClasses = (int) $metadata['output_shape'][0];
            }
        } catch (\Throwable $e) {
            // ignore; dummy will fallback later
        }
        if ($classesOpt && is_numeric($classesOpt)) {
            $outputClasses = (int) $classesOpt;
        }
        if ($useDummy && (!$outputClasses || $outputClasses < 2)) {
            $outputClasses = 3; // default
        }

        $fh = fopen($file, 'r');
        if ($fh === false) {
            $this->error('تعذّر فتح الملف');
            return self::FAILURE;
        }

        $header = null;
        if (!$noHeader) {
            $header = fgetcsv($fh, 0, $sep);
            if ($header === false) {
                $this->error('تعذّر قراءة رأس الأعمدة');
                fclose($fh);
                return self::FAILURE;
            }
        }

        // Resolve column indexes
        $featureIdx = [];
        $labelIdx = null;
        if ($noHeader) {
            // indices mode
            foreach ($featuresCols as $fc) {
                if (!is_numeric($fc)) {
                    $this->error('عند استخدام --no-header يجب أن تكون --features فهارس رقمية 0..N');
                    fclose($fh);
                    return self::FAILURE;
                }
                $featureIdx[] = (int) $fc;
            }
            if (!is_numeric($labelCol)) {
                $this->error('عند استخدام --no-header يجب أن يكون --label فهرساً رقمياً');
                fclose($fh);
                return self::FAILURE;
            }
            $labelIdx = (int) $labelCol;
        } else {
            // names mode
            $map = [];
            foreach ($header as $i => $name) {
                $map[$name] = $i;
            }
            foreach ($featuresCols as $fc) {
                if (!array_key_exists($fc, $map)) {
                    $this->error('اسم عمود الميزة غير موجود في الرأس: ' . $fc);
                    fclose($fh);
                    return self::FAILURE;
                }
                $featureIdx[] = $map[$fc];
            }
            if (!array_key_exists($labelCol, $map)) {
                $this->error('اسم عمود الوسم غير موجود في الرأس: ' . $labelCol);
                fclose($fh);
                return self::FAILURE;
            }
            $labelIdx = $map[$labelCol];
        }

        $trueLabels = [];
        $predLabels = [];
        $rowCount = 0;

        while (($row = fgetcsv($fh, 0, $sep)) !== false) {
            $rowCount++;
            $features = [];
            foreach ($featureIdx as $idx) {
                $val = $row[$idx] ?? null;
                if ($val === null || $val === '') {
                    $features[] = 0.0; // fallback
                } else {
                    $features[] = (float) $val;
                }
            }
            $label = $row[$labelIdx] ?? null;
            $trueLabels[] = $label === null ? 'null' : $label;

            if ($useDummy) {
                // بسيط: اجمع الميزات وطبّق modulo بعدد الفئات
                $sum = array_sum($features);
                $predLabels[] = $outputClasses ? ((int) round($sum)) % $outputClasses : null;
            } else {
                try {
                    $res = $this->aiClient->predict($features);
                    $pred = $res['top_class'] ?? null;
                    $predLabels[] = $pred;
                } catch (\Throwable $e) {
                    $this->warn('تعذّر التنبؤ على صف #' . $rowCount . ': ' . $e->getMessage());
                    $predLabels[] = null; // treat as unknown
                }
            }
        }
        fclose($fh);

        $metrics = Metrics::compute($trueLabels, $predLabels);

        // Output summary
        $this->info('ملخّص التقييم');
        $this->line('عدد الصفوف: ' . $rowCount);
        $this->line(sprintf('الدقة: %.4f', $metrics['accuracy']));
        if ($metrics['macroF1'] !== null) {
            $this->line(sprintf('Macro F1: %.4f', $metrics['macroF1']));
        }

        // Per label metrics
        $this->info('مقاييس لكل فئة:');
        $rows = [];
        foreach ($metrics['labels'] as $label) {
            $pl = $metrics['perLabel'][$label] ?? ['precision' => null, 'recall' => null, 'f1' => null];
            $rows[] = [
                'label' => $label,
                'precision' => $pl['precision'] === null ? '-' : sprintf('%.4f', $pl['precision']),
                'recall' => $pl['recall'] === null ? '-' : sprintf('%.4f', $pl['recall']),
                'f1' => $pl['f1'] === null ? '-' : sprintf('%.4f', $pl['f1']),
            ];
        }
        $this->table(['Label', 'Precision', 'Recall', 'F1'], $rows);

        // Confusion matrix
        $this->info('مصفوفة الالتباس:');
        $headerRow = array_merge(['True \ Pred'], $metrics['labels']);
        $tableRows = [];
        foreach ($metrics['labels'] as $tLabel) {
            $row = [$tLabel];
            foreach ($metrics['labels'] as $pLabel) {
                $row[] = $metrics['confusion'][$tLabel][$pLabel] ?? 0;
            }
            $tableRows[] = $row;
        }
        $this->table($headerRow, $tableRows);

        // Optional: write JSON output
        if ($outPath) {
            $summary = [
                'file' => $file,
                'features' => $featuresCols,
                'label' => $labelCol,
                'sep' => $sep,
                'no_header' => $noHeader,
                'predictor' => $useDummy ? 'dummy' : 'service',
                'classes' => $outputClasses,
                'rows' => $rowCount,
                'metrics' => [
                    'accuracy' => $metrics['accuracy'],
                    'macroF1' => $metrics['macroF1'],
                    'perLabel' => $metrics['perLabel'],
                    'confusion' => $metrics['confusion'],
                    'labels' => $metrics['labels'],
                ],
            ];
            $json = json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            try {
                $dir = dirname($outPath);
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }
                file_put_contents($outPath, $json);
                $this->info('تم حفظ النتائج في: ' . $outPath);
            } catch (\Throwable $e) {
                $this->warn('تعذّر حفظ ملف النتائج: ' . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
