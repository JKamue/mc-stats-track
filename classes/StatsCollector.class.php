<?php


class StatsCollector
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function saveHourlyStats(string $serverId)
    {
        $time = (new DateTime())->format('Y-m-d H:00:00');

        $alreadySaved = $this->db->query("SELECT '' FROM `data` WHERE `server` = ? AND `when` = ?")
            ->bind(1, $serverId)
            ->bind(2, $time)
            ->count();

        if ($alreadySaved > 0) {
            return;
        }

        $serverAdress = $this->db->query("SELECT adress FROM servers WHERE server = ?")
            ->bind(1, $serverId)
            ->single()["adress"];

        $minecraftPingData = \xPaw\MinecraftPing::getPingData($serverAdress);

        $this->db->query("INSERT INTO `data` (`when`, `online`, `max`, `server`) VALUES (?, ?, ?, ?)")
            ->bind(1, $time)
            ->bind(2, $minecraftPingData["players"]["online"])
            ->bind(3, $minecraftPingData["players"]["max"])
            ->bind(4, $serverId)
            ->execute();
    }
}