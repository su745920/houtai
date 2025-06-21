 <?php
 // 生成30minK线
    private function fake_period_kline($config, $start_date, $end_date)
    {
        $decimal = 100000;
        $seconds = 1800;
        $period = 'is_30min';

        $open_price = $config['open'];
        $close_price = $config['close'];
        $high_price = $config['high'];
        $low_price = $config['low'];
        $min_amount = $config['min_amount'];
        $max_amount = $config['max_amount'];
        $start = strtotime($start_date);
        $end = strtotime($end_date);

        //        dd($open_price,$close_price,$high_price,$low_price);

        $period_seconds = 1800;
        $periodCount = 86400 / $period_seconds;
        $unit = custom_number_format(bcMath(($high_price - $low_price), $periodCount - 1, '/', 8), 8);

        // 24小时 周期价格上涨下跌趋势随机 1涨2跌
        $period2_seconds = 1800;
        $periodCount2 = 86400 / $period2_seconds;
        $periodsTrend = [];
        for ($i = 0; $i < $periodCount2; $i++) {
            if ($close_price > $open_price) {
                $thresholdValue = 60;
            } else {
                $thresholdValue = 40;
            }
            $periodsTrend[$start + ($i * $period2_seconds)] = mt_rand(1, 100) <= $thresholdValue ? 1 : 2;
        }

        $ups_downs_high     = abs(floor($unit * $decimal * 8));            //高
        $ups_downs_value    = abs(floor($unit * 3 * $decimal));            //值
        $ups_downs_low      = abs($unit);              //低

        //        dd($unit,$ups_downs_high,$ups_downs_value,$ups_downs_low);

        $current = $start;
        $kkk = 1;
        while ($current < $end) {
            //            echo $current . '--' . $kkk . "\r\n";
            // 获取上一条
            $prev_time = $current - $seconds;
            $prev = array_first($this->klineData ?? [], function ($v, $k) use ($prev_time) {
                return $v['timestamp'] == $prev_time;
            });
            if (blank($prev)) {
                $price = $open_price * $decimal;
            } else {
                $price = $prev['close'] * $decimal;
            }
            $amount = mt_rand($min_amount * $decimal, $max_amount * $decimal) / $decimal;
            $volume = bcMath($amount, $price, '/');
            $open = $price;
            $up_or_down = mt_rand(1, 100);

            $flag = array_first($periodsTrend, function ($v, $k) use ($current, $period2_seconds) {
                return $current >= $k && $current < ($k + $period2_seconds);
            });
            //            dump($current . '--' . $flag);
            if ($flag == 1) {
                $value = 80;
            } else {
                $value = 20;
            }

            // dump($ups_downs_low,$ups_downs_value,$ups_downs_high);
            if ($up_or_down <= $value) {
                // 涨
                $close = mt_rand($price, $price + mt_rand($ups_downs_low, $ups_downs_high));
                $high = mt_rand($close, $close + mt_rand($ups_downs_low, $ups_downs_value));
                $low = mt_rand($close - mt_rand($ups_downs_low, $ups_downs_value), $close);
            } else {
                // 跌
                $close = mt_rand($price - mt_rand($ups_downs_low, $ups_downs_high), $price);
                $high = mt_rand($close, $close + mt_rand($ups_downs_value, $ups_downs_high));
                $low = mt_rand($close - mt_rand($ups_downs_low, $ups_downs_value), $close);
            }

            if ($current == $start) {
                $open = $open_price * $decimal;
            } elseif ($current + $seconds == $end) {
                $close = $close_price * $decimal;
            }

            $high = max($open, $close, $high, $low);
            $low = min($open, $close, $high, $low);

            $open = $open / $decimal;
            $close = $close / $decimal;
            $high = $high / $decimal;
            $low = $low / $decimal;

            //            dd($open,$close,$high,$low,$prev->toArray());
            $klineItem = [
                'timestamp' => $current,
                'datetime' => date('Y-m-d H:i:s', $current),
                'open' => $open,
                'close' => $close,
                'high' => $high,
                'low' => $low,
                'volume' => $volume,
                'amount' => $amount,
                $period => 1,
            ];
            array_push($this->klineData, $klineItem);

            $current += $seconds;
            $kkk++;
        }

        return true;
    }
