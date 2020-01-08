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
        $serverAdress = $this->db->query("SELECT adress FROM servers WHERE server = ?")
            ->bind(1, $serverId)
            ->single()["adress"];

        $minecraftPingData = \xPaw\MinecraftPing::getPingData($serverAdress);

        $time = (new DateTime())->format('Y-m-d H:00:00');
        $this->db->query("INSERT INTO `data` (`when`, `online`, `max`, `server`) VALUES (?, ?, ?, ?)")
            ->bind(1, $time)
            ->bind(2, $minecraftPingData["players"]["online"])
            ->bind(3, $minecraftPingData["players"]["max"])
            ->bind(4, $serverId)
            ->execute();
    }
}