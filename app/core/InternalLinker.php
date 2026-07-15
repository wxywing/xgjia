<?php
/**
 * InternalLinker - 文章内链处理
 * 自动将关键词替换为站内链接
 */

class InternalLinker
{
    /**
     * 内链配置：关键词 => URL
     * 可在后台管理或配置文件中维护
     */
    private static $links = [
        // 工具页
        '血统证书' => '/pigeon/',
        '足环查询' => '/',
        '足环号查询' => '/',
        '赛鸽查询' => '/',
        '公棚查询' => '/lofts/',
        '铭鸽展厅' => '/pigeon/',
        '公棚对比' => '/loft/compare/',
        
        // 品系页 — /pedigree/strain/{品系名}/
        '詹森'   => '/pedigree/strain/詹森/',
        '胡本'   => '/pedigree/strain/胡本/',
        '戈马力' => '/pedigree/strain/戈马力/',
        '克拉克' => '/pedigree/strain/克拉克/',
        '桑杰士' => '/pedigree/strain/桑杰士/',
        '配对'   => '/pairing/',

        // 标签页 — /tag/{slug}/
        '赛前调整' => '/tag/saiqian-tiaozheng/',
        '幼鸽管理' => '/tag/younge-guanli/',
        '疾病防治' => '/tag/jibing-fangzhi/',
    ];

    /**
     * 处理内容，添加内链
     * @param string $content HTML内容
     * @param int $maxLinks 最大内链数量（避免过度优化）
     * @return string 处理后的内容
     */
    public static function process($content, $maxLinks = 5)
    {
        $linkCount = 0;

        // 按关键词长度降序排列（优先匹配长关键词）
        $links = self::$links;
        uksort($links, function($a, $b) {
            return mb_strlen($b) - mb_strlen($a);
        });

        foreach ($links as $keyword => $url) {
            if ($linkCount >= $maxLinks) break;

            $pattern = '/(' . preg_quote($keyword, '/') . ')/u';
            if (preg_match_all($pattern, $content, $m, PREG_OFFSET_CAPTURE) === 0) continue;

            foreach ($m[0] as $match) {
                if ($linkCount >= $maxLinks) break;
                $keywordText = $match[0];
                $absPos = $match[1];

                // 检查是否在 <a>...</a> 标签内
                $before = mb_substr($content, 0, $absPos);
                $open  = mb_substr_count($before, '<a');
                $close = mb_substr_count($before, '</a>');
                if ($open > $close) continue; // 在链接内，跳过

                // 替换
                $content = mb_substr($content, 0, $absPos)
                    . '<a href="' . $url . '" style="color:#1a5fa8;text-decoration:underline;">' . $keywordText . '</a>'
                    . mb_substr($content, $absPos + mb_strlen($keywordText));
                $linkCount++;
            }
        }

        return $content;
    }

    public static function addLink($keyword, $url)
    {
        self::$links[$keyword] = $url;
    }

    public static function addLinks(array $links)
    {
        foreach ($links as $keyword => $url) {
            self::$links[$keyword] = $url;
        }
    }

    public static function getLinks()
    {
        return self::$links;
    }

    /**
     * 根据文本内容，返回匹配到的标签 slug 数组
     * 供发布/编辑文章时调用
     */
    public static function detectTagSlugs($text)
    {
        $slugs = [];
        $tagMap = [
            '赛前调整'  => 'saiqian-tiaozheng',
            '赛前'      => 'saiqian-tiaozheng',
            '状态'      => 'saiqian-tiaozheng',
            '幼鸽'      => 'younge-guanli',
            '雏鸽'      => 'younge-guanli',
            '幼鸽管理'  => 'younge-guanli',
            '送公棚'    => 'younge-guanli',
            '公棚赛'    => 'gongpengsai',
            '公棚'      => 'gongpengsai',
            '协会赛'    => 'xiehuisai',
            '协会'      => 'xiehuisai',
            '血统'      => 'xuetong',
            '詹森'      => 'zhansen',
            '胡本'      => 'huben',
            '桑杰士'    => 'sangjieshi',
            '克拉克'    => 'kelake',
            '戈马力'    => 'gemali',
            '训放'      => 'xunfang',
            '家飞'      => 'xunfang',
            '配对'      => 'peidui',
            '归巢'      => 'guichao',
            '归巢率'    => 'guichao',
            '分速'      => 'fensu',
            '速度'      => 'fensu',
            '疾病'      => 'jibing-fangzhi',
            '生病'      => 'jibing-fangzhi',
            '防治'      => 'jibing-fangzhi',
            '预防'      => 'jibing-fangzhi',
            '健康'      => 'jibing-fangzhi',
            '秋赛'      => '2026-qiusai',
            '春赛'      => '2026-chunsai',
        ];

        foreach ($tagMap as $keyword => $slug) {
            if (mb_strpos($text, $keyword) !== false) {
                $slugs[$slug] = true;
            }
        }

        return array_keys($slugs);
    }
}
