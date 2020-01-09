<?php

class StatsReader
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getHourlyStats(int $numberOfHours, string $serverId)
    {
        return $this->db->query("SELECT DATE(`when`) AS 'date', HOUR(`when`) AS 'hour', FLOOR(AVG(`online`)) AS 'online',
                                        FLOOR(AVG(`ping`)) AS 'ping', MAX(`max`)  AS 'max'  
                                 FROM data WHERE server = ?
                                 GROUP BY DATE(`when`) DESC, HOUR(`when`) DESC LIMIT ?")
            ->bind(1, $serverId)
            ->bind(2, $numberOfHours)
            ->all();
    }

    public function getDailyStats(int $numberOfDays, string $serverId)
    {
        return $this->db->query("SELECT DATE(`when`) AS 'date', FLOOR(AVG(`online`)) AS 'online',
                                        FLOOR(AVG(`ping`)) AS 'ping', MAX(`max`)  AS 'max'  
                                 FROM data WHERE server = ?
                                 GROUP BY DATE(`when`) DESC LIMIT ?")
            ->bind(1, $serverId)
            ->bind(2, $numberOfDays)
            ->all();
    }

    public function getWeeklyStats(int $numberOfWeeks, string $serverId)
    {
        return $this->db->query("SELECT YEAR(`when`) AS 'year', 
                                        IF(WEEKOFYEAR(`when`)='1' and MONTH(`when`)='12' ,52,WEEKOFYEAR(`when`)) AS 'week',
                                        FLOOR(AVG(`online`)) AS 'online', FLOOR(AVG(`ping`)) AS 'ping', MAX(`max`) AS 'max'
                                  FROM data WHERE server = ? GROUP BY YEAR(`when`) DESC,
                                        IF(WEEKOFYEAR(`when`)='1' and MONTH(`when`)='12' ,52,WEEKOFYEAR(`when`)) DESC LIMIT ?")
            ->bind(1, $serverId)
            ->bind(2, $numberOfWeeks)
            ->all();
    }

}