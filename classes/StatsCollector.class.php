<?php


class StatsCollector
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function saveAllHourlyStats(): array
    {
        $servers = $this->db->query("SELECT `server` AS 'serverId', `adress` FROM `servers`")
            ->all();

        $stats = array(
            "done" => 0,
            "failed" => 0
        );

        foreach ($servers as &$server) {
            $status = $this->saveHourlyStats($server["serverId"], $server["adress"]);
            $stats[$status ? "done" : "failed"] += 1;
        }

        return $stats;
    }

    public function saveHourlyStats(string $serverId, string $serverAdress): bool
    {
        $time = (new DateTime())->format('Y-m-d H:00:00');

        $alreadySaved = $this->db->query("SELECT '' FROM `data` WHERE `server` = ? AND `when` = ?")
            ->bind(1, $serverId)
            ->bind(2, $time)
            ->count();

        if ($alreadySaved > 0) {
            return true;
        }

        try {
            $minecraftPingData = MinecraftPing::getPingData($serverAdress);

            if ($minecraftPingData["players"]["online"] == NULL) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }

        $this->db->query("INSERT INTO `data` (`when`, `online`, `max`, `ping`, `server`) VALUES (?, ?, ?, ?, ?)")
            ->bind(1, $time)
            ->bind(2, $minecraftPingData["players"]["online"])
            ->bind(3, $minecraftPingData["players"]["max"])
            ->bind(4, $minecraftPingData["responseTime"])
            ->bind(5, $serverId)
            ->execute();

        return true;
    }
}