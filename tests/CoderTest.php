<?php

namespace Intaro\HStore\Tests;

use Intaro\HStore\Coder;
use PHPUnit\Framework\TestCase;

class CoderTest extends TestCase
{
    public function dataDecode()
    {
        return [
            ['', []],
            ['    ', []],
            ["\t \n \r", []],

            ['a=>b', ['a' => 'b']],
            [' a=>b', ['a' => 'b']],
            [' a =>b', ['a' => 'b']],
            [' a => b', ['a' => 'b']],
            [' a => b ', ['a' => 'b']],
            ['a => b ', ['a' => 'b']],
            ['a=> b ', ['a' => 'b']],
            ['a=>b ', ['a' => 'b']],

            ['"a"=>"b"', ['a' => 'b']],
            [' "a"=>"b"', ['a' => 'b']],
            [' "a" =>"b"', ['a' => 'b']],
            [' "a" => "b"', ['a' => 'b']],
            [' "a" => "b" ', ['a' => 'b']],
            ['"a" => "b" ', ['a' => 'b']],
            ['"a"=> "b" ', ['a' => 'b']],
            ['"a"=>"b" ', ['a' => 'b']],

            ['aa=>bb', ['aa' => 'bb']],
            [' aa=>bb', ['aa' => 'bb']],
            [' aa =>bb', ['aa' => 'bb']],
            [' aa => bb', ['aa' => 'bb']],
            [' aa => bb ', ['aa' => 'bb']],
            ['aa => bb ', ['aa' => 'bb']],
            ['aa=> bb ', ['aa' => 'bb']],
            ['aa=>bb ', ['aa' => 'bb']],

            ['"aa"=>"bb"', ['aa' => 'bb']],
            [' "aa"=>"bb"', ['aa' => 'bb']],
            [' "aa" =>"bb"', ['aa' => 'bb']],
            [' "aa" => "bb"', ['aa' => 'bb']],
            [' "aa" => "bb" ', ['aa' => 'bb']],
            ['"aa" => "bb" ', ['aa' => 'bb']],
            ['"aa"=> "bb" ', ['aa' => 'bb']],
            ['"aa"=>"bb" ', ['aa' => 'bb']],

            ['aa=>bb, cc=>dd', ['aa' => 'bb', 'cc' => 'dd']],
            ['aa=>bb , cc=>dd', ['aa' => 'bb', 'cc' => 'dd']],
            ['aa=>bb ,cc=>dd', ['aa' => 'bb', 'cc' => 'dd']],
            ['aa=>bb, "cc"=>dd', ['aa' => 'bb', 'cc' => 'dd']],
            ['aa=>bb , "cc"=>dd', ['aa' => 'bb', 'cc' => 'dd']],
            ['aa=>bb ,"cc"=>dd', ['aa' => 'bb', 'cc' => 'dd']],
            ['aa=>"bb", cc=>dd', ['aa' => 'bb', 'cc' => 'dd']],
            ['aa=>"bb" , cc=>dd', ['aa' => 'bb', 'cc' => 'dd']],
            ['aa=>"bb" ,cc=>dd', ['aa' => 'bb', 'cc' => 'dd']],

            ['aa=>null',   ['aa' => null]],
            ['aa=>NuLl',   ['aa' => null]],
            ['aa=>"NuLl"', ['aa' => "NuLl"]],
            ['aa=>nulla',  ['aa' => "nulla"]],

            ['a=>5',   ['a' => '5']],
            ['a=>5.5', ['a' => '5.5']],
            ['5=>1',   [5 => "1"]],

            ['"a"=>"==>,\\""', ['a' => '==>,"']],

            ['a=>b,',   ['a' => 'b']],
            ['a=>b ,',  ['a' => 'b']],
            ['a=>b, ',  ['a' => 'b']],
            ['a=>b , ', ['a' => 'b']],

            ['a=>""', ['a' => '']],
            ['""=>"\\""', ['' => '"']],
            ['\"a=>q"w',   ['"a' => 'q"w']],

            ['>,=>q=w,',   ['>,' => 'q=w']],
            ['>, =>q=w,',   ['>,' => 'q=w']],
            ['>, =>q=w ,',   ['>,' => 'q=w']],
            ['>, =>q=w , ',   ['>,' => 'q=w']],
            ['>,=>q=w , ',   ['>,' => 'q=w']],
            ['>,=>q=w, ',   ['>,' => 'q=w']],

            //['\=a=>q=w',   ['=a' => 'q=w']],
            ['"=a"=>q\=w', ['=a' => 'q=w']],
            ['"\"a"=>q>w', ['"a' => 'q>w']],
        ];
    }

    /**
     * @dataProvider dataDecode
     *
     * @param string $data
     * @param array  $expected
     */
    public function testDecode($data, array $expected): void
    {
        $this->assertSame($expected, Coder::decode($data));
    }

    public function dataEncode()
    {
        return [
            [[], ''],
            [['a' => ''], '"a"=>""'],
            [['' => 'a'], '""=>"a"'],
            [['' => '"'], '""=>"\\""'],

            [['a' => 'b"'], '"a"=>"b\\""'],

            [['a' => 'b', 'c' => 'd'], '"a"=>"b", "c"=>"d"'],

            [['a' => null], '"a"=>NULL'],
            [['a"' => '"b\\'], '"a\\""=>"\\"b\\\\"'],

            [['a' => 5], '"a"=>"5"'],
            [['a' => 5.5], '"a"=>"5.5"'],
            [['a', "b"], '"0"=>"a", "1"=>"b"'],
        ];
    }

    /**
     * @dataProvider dataEncode
     *
     * @param array  $data
     * @param string $expected
     */
    public function testEncode(array $data, $expected): void
    {
        $this->assertSame($expected, Coder::encode($data));
    }

    public function testExtension(): void
    {
        if (!extension_loaded('hstore')) {
            $this->markTestSkipped();
        }

        $r = new \ReflectionExtension('hstore');

        $this->assertContains('Intaro\\HStore\\Coder', $r->getClassNames());
    }

    public function testMemoryUsage(): void
    {
        gc_collect_cycles();

        $var = [
            'a' => 'b',
            'c' => null,
            'd' => 'e"e',
            'f' => str_repeat('0', 1024),
        ];

        set_error_handler(function () {
            // noop
        });

        try {
            $i = 0;

            $before = memory_get_usage();
            $real_before = memory_get_usage(true);

            for (; $i < 10000; $i++) {
                Coder::decode(Coder::encode($var));
            }
        } finally {
            restore_error_handler();
        }

        gc_collect_cycles();

        $after = memory_get_usage();
        $real_after = memory_get_usage(true);
        $this->assertLessThanOrEqual($before, $after, sprintf("Memory is corrupted (%d bytes not cleared)", $after - $before));
        $this->assertLessThanOrEqual($real_before, $real_after, sprintf("Real memory is corrupted (%d bytes not cleared)", $real_after - $real_before));
    }
}
