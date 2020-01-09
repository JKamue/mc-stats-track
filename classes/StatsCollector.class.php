<?php


class StatsCollector
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function saveAllHourlyStats()
    {
        $servers = $this->db->query("SELECT `server` AS 'serverId', `adress` FROM `servers`")
            ->all();

        foreach ($servers as &$server) {
            $this->saveHourlyStats($server["serverId"], $server["adress"]);
        }
    }

    public function saveHourlyStats(string $serverId, string $serverAdress)
    {
        $time = (new DateTime())->format('Y-m-d H:00:00');

        $alreadySaved = $this->db->query("SELECT '' FROM `data` WHERE `server` = ? AND `when` = ?")
            ->bind(1, $serverId)
            ->bind(2, $time)
            ->count();

        if ($alreadySaved > 0) {
            return;
        }

        $minecraftPingData = \xPaw\MinecraftPing::getPingData($serverAdress);

        $this->db->query("INSERT INTO `data` (`when`, `online`, `max`, `ping`, `server`) VALUES (?, ?, ?, ?, ?)")
            ->bind(1, $time)
            ->bind(2, $minecraftPingData["players"]["online"])
            ->bind(3, $minecraftPingData["players"]["max"])
            ->bind(4, $minecraftPingData["responseTime"])
            ->bind(5, $serverId)
            ->execute();
    }
}