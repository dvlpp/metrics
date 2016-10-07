<?php

use Carbon\Carbon;
use Dvlpp\Metrics\TimeInterval;
use Dvlpp\Metrics\Metric;
use Illuminate\Support\Collection;
use Dvlpp\Metrics\Analyzers\UrlAnalyzer;
use Dvlpp\Metrics\Analyzers\UserAgentAnalyzer;

class UserAgentAnalyzerTest extends MetricTestCase
{
    /** @test */
    public function test_user_agent_analyzer()
    {
        $analyzer = new UserAgentAnalyzer();
        $visits = $this->generateVisits(100);
        $result = $analyzer->compile($visits);
        $this->assertEquals(100, array_sum($result['categories'])); 
    }

    /** @test */
    public function test_user_agent_consolider()
    {
        $analyzer = new UserAgentAnalyzer();
        $visits = $this->generateVisits(50);
        $resultA = [UserAgentAnalyzer::class => $analyzer->compile($visits)];
        $visits = $this->generateVisits(50);
        $resultB = [UserAgentAnalyzer::class => $analyzer->compile($visits)];
        $metrics = new Collection([
            $this->generateMetric($resultA, 50),
            $this->generateMetric($resultB, 50),
        ]);
        $result = $analyzer->consolidate($metrics);
        $this->assertEquals(100, array_sum($result['categories']));
    }

    /** @test */
    public function test_add_array_values_method()
    {
        $analyzer = new UserAgentAnalyzer();
        $a = [
            'foo' => [
                "val1" => 2,
                "val2" => 3,
            ],
            'bar' => [
                "val3" => 1,
            ]
        ];
        $b = [
            'foo' => [
                "val1" => 2,
                "val4" => 3,
            ],
            'bar' => [
            ]
        ];

        $c = $analyzer->addArrayValues($a,$b);
        $this->assertEquals([
            'foo' => [
                "val1" => 4,
                "val2" => 3,
                "val4" => 3,
            ],
            'bar' => [
                "val3" => 1,
            ]
        ], $c);
    }

    /** @test */
    public function add_array_preserve_keys_if_no_values()
    {
        $analyzer = new UserAgentAnalyzer();
        $a = [
            'foo' => [
            ],
            'bar' => [
            ]
        ];
        $b = [
            'foo' => [
            ],
            'bar' => [
            ]
        ];
        $c = $analyzer->addArrayValues($a,$b);
        $this->assertEquals($b,$c);
    }

    /**
     * Generate Metric Object
     * 
     * @param  array  $results
     * @return Metric
     */
    protected function generateMetric(array $results, $count)
    {
        return Metric::create(
            new TimeInterval(Carbon::now(), Carbon::now()),
            $results,
            $count
        );
    }

}
