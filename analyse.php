<?php

class Stat
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var Item[]
     */
    protected $input;
    /** @var  Item[] */
    protected $output;

    /**
     * Stat constructor.
     * @param Item[] $output
     * @param Item[] $input
     * @param string $name
     */
    public function __construct(array $output, array $input, $name)
    {
        $this->output = $output;
        $this->input = $input;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Item[]
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return Item[]
     */
    public function getOutput()
    {
        return $this->output;
    }

    public function getInputForName($name)
    {
        if (isset($this->input[$name])) {
            return $this->input[$name]->getAmount();
        }
        return 0;
    }

    public function getOutputForName($name)
    {
        if (isset($this->output[$name])) {
            return $this->output[$name]->getAmount();
        }
        return 0;
    }

    public function getCompareHash(Stat $stat)
    {
        $hash = array();

        foreach (array(
                     'input',
                     'output'
                 ) as $key) {
            $hash[$key] = array();

            foreach ($this->$key as $name => $item) {
                $callName = 'get' . ucfirst($key) . 'ForName';
                $leftAmount = $item->getAmount();
                $rightAmount = $stat->$callName($name);
                $h = array(
                    'name' => $name,
                    'image' => $item->getImage(),
                    'left' => array(
                        'amount' => $leftAmount,
                        'better' => $leftAmount > $rightAmount,
                    ),
                    'right' => array(
                        'amount' => $rightAmount,
                        'better' => $rightAmount > $leftAmount
                    )
                );
                $hash[$key] = $h;
            }
        }

        return $hash;
    }

}

class Item
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var float
     */
    protected $amount;

    /**
     * Item constructor.
     * @param string $name
     * @param float $amount
     */
    public function __construct($name, $amount)
    {
        $this->name = $name;
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    public function getImage()
    {
        $filePath = __DIR__ . DIRECTORY_SEPARATOR;
        $fileName = 'graphics' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . $this->getName() . '.png';
//        var_dump($fileName);
//        exit;
        if (file_exists($filePath . $fileName)) {
            return str_replace(DIRECTORY_SEPARATOR, '/', $fileName);
        }
        return null;
    }

    protected function getOriginalName()
    {
        return getItemName($this->getName());
    }
}

class Force
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var Stat[]
     */
    protected $stats;
    /**
     * @var array
     */
    protected $statsHash;
    /**
     * @var array
     */
    protected $data;

    /**
     * @var Diff
     */
    protected $diff;

    /**
     * Force constructor.
     * @param string $id
     * @param array $stats
     * @param array $data
     */
    public function __construct($id, array $stats, array $data)
    {
        $this->id = $id;

        foreach ($stats as $name => $d) {
            $input = array();
            $output = array();

            foreach ($d['input'] as $itemName => $itemValue) {
                $input[$itemName] = new Item($itemName, $itemValue);
            }
            foreach ($d['output'] as $itemName => $itemValue) {
                $output[$itemName] = new Item($itemName, $itemValue);
            }

            $s = new Stat($output, $input, $name);
            $this->stats[$name] = $s;
            $this->statsHash = $stats;
        }

        $this->data = $data;
    }

    /**
     * @return FullDiff
     */
    public function getDiff()
    {
        if ($this->diff === null) {
            $this->diff = new FullDiff($this);
        }
        return $this->diff;
    }


    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function getColorAsHex()
    {
        if (!isset($this->data['color'])) {
            return $this->getColorByName($this->id);
        }
        $c = $this->data['color'];
        foreach ($c as $key => $value) {
            if ($key === 'a') continue;
            $c[$key] = $value * 255;
        }
        return $this->RGBToHex($c['r'], $c['g'], $c['b']);
    }

    protected function getColorByName($id)
    {
        switch ($id) {
            case 'enemy':
                return '#ff0000';
            case 'player':
                return '#3131A2';
            case 'neutral':
                return '#5BC85B';
        }
        return '#000000';
    }

    protected function RGBToHex($r, $g, $b)
    {
        //String padding bug found and the solution put forth by Pete Williams (http://snipplr.com/users/PeteW)
        $hex = "#";
        $hex .= str_pad(dechex($r), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($g), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($b), 2, "0", STR_PAD_LEFT);

        return $hex;
    }

    /**
     * @return Stat
     */
    public function getItemResourceStatistic()
    {
        return $this->getStatForName('item_resource_statistics');
    }

    /**
     * @return Stat
     */
    public function getKillCountStatistic()
    {
        return $this->getStatForName('kill_count_statistics');
    }

    /**
     * @return Stat
     */
    public function getItemProductionStatistic()
    {
        return $this->getStatForName('item_production_statistics');
    }

    /**
     * @return Stat
     */
    public function getFluidProductionStatistic()
    {
        return $this->getStatForName('fluid_production_statistics');
    }

    /**
     * @return Stat
     */
    public function getFluidResourceStatistic()
    {
        return $this->getStatForName('fluid_resource_statistics');
    }

    /**
     * @return Stat
     */
    public function getEntityBuildCountStatistic()
    {
        return $this->getStatForName('entity_build_count_statistics');
    }

    /**
     * @return Stat[]
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * @param string $name
     * @return Stat
     */
    public function getStatForName($name)
    {
        return $this->stats[$name];
    }

    public function getCompareHash(Force $force)
    {
        $h = array();

        foreach ($this->getStats() as $s) {
            $h[$s->getName()] = $s->getCompareHash($force->getStatForName($s->getName()));
        }

        return $h;
    }

    public function getName()
    {
        if (isset($this->data['title'])) {
            return $this->data['title'];
        }
        switch ($this->id) {
            case 'enemy':
                return 'Aliens';
            case 'player':
                return 'Lobby';
            case 'neutral':
                return 'Neutral';
        }
        return 'Nicht definiert';
    }

    public function toHash()
    {
        $h = array(
            'id' => $this->id,
            'name' => $this->getName(),
            'color' => $this->getColorAsHex(),
            'stats' => $this->statsHash
        );

        return $h;
    }
}

class DiffItem
{
    protected $current;
    protected $last;

    protected $name;

    /**
     * DiffItem constructor.
     * @param int $current
     * @param int $last
     */
    public function __construct($name, $current, $last)
    {
        $this->name = $name;
        $this->current = $current;
        $this->last = $last;
    }

    /**
     * @return int
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getLast()
    {
        return $this->last;
    }

    public function getImage()
    {
        $filePath = __DIR__;
        $fileName = DIRECTORY_SEPARATOR . 'graphics' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . $this->getName() . '.png';
//        var_dump($fileName);
//        exit;
        if (file_exists($filePath . $fileName)) {
            return $fileName;
        }
        return null;
    }

    public function getOriginalName()
    {
        return getItemName($this->getName());
    }

    public function getDiff()
    {
        return $this->current - $this->last;
    }

    public function getPercent()
    {
//        if($this->getLast() === 0) {
//            return 100;
//        }
        return number_format((1 - $this->getLast() / $this->getCurrent()) * 100, 2);
    }

    public function toHash()
    {
        return array(
            'name' => $this->getName(),
            'image' => $this->getImage(),
            'diff' => $this->getDiff(),
            'percent' => $this->getPercent()
        );
    }

}

class FullDiff extends Diff
{

    /**
     * @return array()
     */
    public function getAllDiffItems()
    {
        $d = array();

        foreach (array(
                     'item_resource_statistics',
                     'kill_count_statistics',
                     'item_production_statistics',
                     'fluid_production_statistics',
                     'fluid_resource_statistics',
                     'entity_build_count_statistics'
                 ) as $name) {
            $d[$name] = $this->getStatisticForName($name);
        }


        return $d;
    }

    public function getItemResourceStatistic()
    {
        return $this->getStatisticForName('item_resource_statistics');
    }

    public function getKillCountStatistic()
    {
        return $this->getStatisticForName('kill_count_statistics');
    }

    public function getItemProductionStatistic()
    {
        return $this->getStatisticForName('item_production_statistics');
    }

    public function getFluidProductionStatistic()
    {
        return $this->getStatisticForName('fluid_production_statistics');
    }

    public function getFluidResourceStatistic()
    {
        return $this->getStatisticForName('fluid_resource_statistics');
    }

    public function getEntityBuildCountStatistic()
    {
        return $this->getStatisticForName('entity_build_count_statistics');
    }

    protected function getStatisticForName($name)
    {
        $current = $this->getCurrentForName($name);
        $last = $this->getLastForName($name);

        return array(
            'input' => $this->diffInputItems($current, $last, true),
            'output' => $this->diffOutputItems($current, $last, true)
        );

    }

    /**
     * @param $name
     * @return Stat
     */
    protected function getCurrentForName($name)
    {
        return $this->force->getStatForName($name);
    }

    /**
     * @param $name
     * @return Stat
     */
    protected function getLastForName($name)
    {
        if ($this->last === null) {
            return new Stat(array(), array(), $name);
        }
        return $this->last->getStatForName($name);
    }
}

class Diff
{
    /**
     * @var Force
     */
    protected $force;


    /**
     * @var Force
     */
    protected $last = null;

    /**
     * diff constructor.
     * @param Force $force
     */
    public function __construct(Force $force)
    {
        $this->force = $force;
        $this->loadLast();
    }

    protected function loadLast()
    {
        if ($this->last !== null) {
            return;
        }
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'lastJsons';
        $file = $dir . DIRECTORY_SEPARATOR . $this->force->getId() . '.ser';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (file_exists($file)) {
            $t = filemtime($file);
            $this->last = unserialize(file_get_contents($file));
            $original = __DIR__ . DIRECTORY_SEPARATOR . 'jsons' . DIRECTORY_SEPARATOR . $this->force->getId() . '.json';
            if (time()-$t > 60 * 15 && isServerRunning()) {

                if(file_exists($original)) {
                    $f = loadForceByFile($original);
//                    file_put_contents($file, serialize($f));
                }
            }
        }
//        print_r($this->last);
//        exit;
    }

    /**
     * @return DiffItem[]
     */
    protected function getDiffItems()
    {
        return array_merge($this->getDiffItemsProduction(), $this->getDiffItemsFluidProduction());
    }

    protected function getDiffItemsProduction()
    {

        $stats = $this->force->getItemProductionStatistic();
        if ($this->last === null) {
            $last = new Stat(array(), array(), 'item_production_statistics');
        } else {
            $last = $this->last->getItemProductionStatistic();
        }
        $items = array();
        foreach ($stats->getInput() as $item) {
            $items[] = new DiffItem($item->getName(), $item->getAmount(), $last->getInputForName($item->getName()));
        }
        return $items;
    }

    protected function getDiffItemsFluidProduction()
    {
        $stats = $this->force->getFluidProductionStatistic();
        if ($this->last === null) {
            $last = new Stat(array(), array(), 'fluid_production_statistics');
        } else {
            $last = $this->last->getFluidProductionStatistic();
        }
        $items = array();
        foreach ($stats->getInput() as $item) {
            if ($item->getName() === 'water') {
                continue;
            }
            $items[] = new DiffItem($item->getName(), $item->getAmount(), $last->getInputForName($item->getName()));
        }
        return $items;
    }

    /**
     * @param Stat $current
     * @param Stat $last
     * @param $type
     * @return DiffItem[]
     */
    private function diffItems(Stat $current, Stat $last, $type, $hash = false)
    {
        $items = array();
        $f = $type . 'ForName';
        foreach ($current->$type() as $item) {
            if ($item->getName() === 'water') {
                continue;
            }
            $i = new DiffItem($item->getName(), $item->getAmount(), $last->$f($item->getName()));
            if ($hash) {
                $items[$item->getName()] = $i->toHash();
            } else {
                $items[$item->getName()] = $i;
            }
        }
        return $items;
    }

    /**
     * @param Stat $current
     * @param Stat $last
     * @return DiffItem[]
     */
    protected function diffInputItems(Stat $current, Stat $last, $hash = false)
    {
        return $this->diffItems($current, $last, 'getInput', $hash);
    }

    /**
     * @param Stat $current
     * @param Stat $last
     * @return DiffItem[]
     */
    protected function diffOutputItems(Stat $current, Stat $last, $hash = false)
    {
        return $this->diffItems($current, $last, 'getOutput', $hash);
    }

    /**
     * @return DiffItem[]
     */
    protected function getSortedDiffItems()
    {
        $items = $this->getDiffItems();

        usort($items, function (DiffItem $a, DiffItem $b) {
            return $b->getPercent() - $a->getPercent();
        });
        return $items;
    }

    /**
     * @param int $length
     * @return DiffItem[]
     */
    public function getTopDiffItems($length = 3)
    {
        return array_slice($this->getSortedDiffItems(), 0, $length);
    }

    public function save()
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'lastJsons';
        $file = $dir . DIRECTORY_SEPARATOR . $this->force->getId() . '.ser';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($file, serialize($this->force));
    }
}

/**
 * @return Force[]
 */
function getForces()
{
    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'jsons';

    $f = array();

    $files = array_diff(scandir($dir), array(
        '.',
        '..'
    ));
    foreach ($files as $file) {
        $force = loadForceByFile($dir . DIRECTORY_SEPARATOR . $file);
        $f[$force->getId()] = $force;
    }

    return $f;
}

/**
 * @return string[]
 */
function getForceIds()
{
    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'jsons';

    $ids = array();

    $files = array_diff(scandir($dir), array(
        '.',
        '..'
    ));
    foreach ($files as $file) {
        $id = str_replace('.json', '', $file);
        $ids[] = $id;
    }
    return $ids;
}

/**
 * @param string $id
 * @return Force|null
 */
function getForceById($id)
{
    $file = __DIR__ . DIRECTORY_SEPARATOR . 'jsons' . DIRECTORY_SEPARATOR . $id . '.json';
    return loadForceByFile($file);
}

/**
 * @return bool
 */
function isServerRunning()
{
    $cmd = "ps ax | grep -v grep | grep \"factorio\" | awk '{ print $1}'";
    exec($cmd, $return);
    return isset($return[0]) && $return[0] > 0;
}

function formatWithSuffix($input)
{
    $suffixes = array(
        '',
        'k',
        'm',
        'g',
        't'
    );
    $suffixIndex = 0;

    while (abs($input) >= 1000 && $suffixIndex < sizeof($suffixes)) {
        $suffixIndex++;
        $input /= 1000;
    }

    return (
    $input > 0
        // precision of 3 decimal places
        ? floor($input * 100) / 100
        : ceil($input * 100) / 100
    )
    . $suffixes[$suffixIndex];
}

function loadForceByFile($file)
{
    if (!file_exists($file)) {
        return null;
    }
    $id = str_replace('.json', '', basename($file));
    $d = json_decode(file_get_contents($file), true);
    return new Force($id, $d['stats'], $d['forceData']);
}


/**
 * @param Force[] $forces
 * return array
 */
function getCompareHashes(array $forces)
{
    $h = array();

    $statsData = array();
    foreach ($forces as $f) {
        foreach ($f->getStats() as $s) {
            foreach ($s->getInput() as $item) {
                $statsData[$s->getName()]['input'][$item->getName()] = array(
                    'name' => $item->getName(),
                    'image' => $item->getImage()
                );
            }
            foreach ($s->getOutput() as $item) {
                $statsData[$s->getName()]['output'][$item->getName()] = array(
                    'name' => $item->getName(),
                    'image' => $item->getImage()
                );
            }

        }
    }

    foreach ($forces as $f) {
        $diffItems = $f->getDiff()->getAllDiffItems();
        foreach ($statsData as $statName => $type) {
            foreach ($type as $t => $items) {
                $call = 'get' . ucfirst($t) . 'ForName';
                foreach ($items as $itemName => $d) {
                    if (!isset($h[$statName][$itemName])) {
                        $h[$statName][$itemName] = array(
                            'name' => $itemName,
                            'image' => $d['image'],
                            'forces' => array()
                        );
                    }
                    $h[$statName][$itemName]['forces'][$f->getName()][$t] = $f->getStatForName($statName)->$call($itemName);
                    $diffType = $t . '_diff';
                    if (isset($diffItems[$statName][$t][$itemName])) {
                        $h[$statName][$itemName]['forces'][$f->getName()][$diffType] = $diffItems[$statName][$t][$itemName];
                    } else {
                        $h[$statName][$itemName]['forces'][$f->getName()][$diffType] = array(
                            'name' => $itemName,
                            'image' => $d['image'],
                            'diff' => 0,
                            'percent' => 0.00
                        );
                    }
                }
            }
        }
    }


    return $h;
}

$names = array(
    "accumulator" => "Akku",
    "arithmetic-combinator" => "Arithmetischer Kombinator",
    "assembling-machine-1" => "Montagemaschine 1",
    "assembling-machine-2" => "Montagemaschine 2",
    "assembling-machine-3" => "Montagemaschine 3",
    "beacon" => "Leuchtfeuer",
    "behemoth-biter" => "Kolossaler Beißer",
    "behemoth-biter-corpse" => "Toter kolossaler Beißer",
    "behemoth-spitter" => "Kolossaler Speier",
    "behemoth-spitter-corpse" => "Toter kolossaler Speier",
    "big-biter" => "Großer Beißer",
    "big-biter-corpse" => "Toter großer Beißer",
    "big-electric-pole" => "Großer Strommast",
    "big-remnants" => "Große Überreste",
    "big-ship-wreck-1" => "Großes Schiffswrack",
    "big-ship-wreck-2" => "Großes Schiffswrack",
    "big-ship-wreck-3" => "Großes Schiffswrack",
    "big-ship-wreck-grass" => "Gras unter dem großen Schiffswrack",
    "big-spitter" => "Großer Speier",
    "big-spitter-corpse" => "Toter großer Speier",
    "big-worm-corpse" => "Toter großer Wurm",
    "big-worm-turret" => "Großer Wurm",
    "biter-spawner" => "Beißernest",
    "biter-spawner-corpse" => "Beißernest-Überreste",
    "boiler" => "Heizkessel",
    "brown-asterisk" => "Brauner Stern",
    "brown-cane-cluster" => "Braunes Schilfrohrbüschel",
    "brown-cane-single" => "Einzelnes braunes Schilfrohr",
    "brown-carpet-grass" => "Brauner Grasteppich",
    "brown-coral-mini" => "Kleine braune Koralle",
    "brown-fluff" => "Braune Flechten",
    "brown-fluff-dry" => "Trockene braune Flechten",
    "brown-hairy-grass" => "Braunes Grasbüschel",
    "burner-inserter" => "Befeuerter Greifarm",
    "burner-mining-drill" => "Befeuerter Erzförderer",
    "car" => "Auto",
    "cargo-wagon" => "Güterwaggon",
    "chemical-plant" => "Chemiefabrik",
    "coal" => "Kohle",
    "constant-combinator" => "Konstanter Kombinator",
    "construction-robot" => "Bauroboter",
    "copper-cable" => "Kupferkabel",
    "copper-ore" => "Kupfererz",
    "crude-oil" => "Rohöl",
    "curved-rail" => "Gebogene Schiene",
    "curved-rail-remnants" => "Überreste einer gebogenen Schiene",
    "dead-dry-hairy-tree" => "Toter vertrockneter borstiger Baum",
    "dead-grey-trunk" => "Toter grauer Baumstumpf",
    "dead-tree" => "Toter Baum",
    "decider-combinator" => "Vergleichender Kombinator",
    "defender" => "Verteidiger",
    "destroyer" => "Zerstörer",
    "diesel-locomotive" => "Diesellokomotive",
    "distractor" => "Ablenker",
    "dry-hairy-tree" => "Vertrockneter borstiger Baum",
    "dry-tree" => "Vertrockneter Baum",
    "electric-furnace" => "Lichtbogenofen",
    "electric-mining-drill" => "Elektrischer Erzförderer",
    "express-loader" => "Express-belader",
    "express-splitter" => "Express-Teilerfließband",
    "express-transport-belt" => "Express-Fließband",
    "express-underground-belt" => "Untergrund-Fließband (express)",
    "fast-inserter" => "Schneller Greifarm",
    "fast-loader" => "Schneller-belader",
    "fast-splitter" => "Schnelles Teilerfließband",
    "fast-transport-belt" => "Schnelles Fließband",
    "fast-underground-belt" => "Untergrund-Fließband (schnell)",
    "filter-inserter" => "Filternder Greifarm",
    "fish" => "Fisch",
    "flamethrower-turret" => "Flammenwerferturm",
    "garballo" => "Fächerpalme",
    "garballo-mini-dry" => "Kleine trockene Fächerpalme",
    "gate" => "Tor",
    "green-asterisk" => "Grüner Stern",
    "green-bush-mini" => "Kleiner grüner Busch",
    "green-carpet-grass" => "Grüner Grasteppich",
    "green-coral" => "Grüne Koralle",
    "green-coral-mini" => "Kleine grüne Koralle",
    "green-hairy-grass" => "Grünes Grasbüschel",
    "green-pita" => "Grüne Agave",
    "green-pita-mini" => "Kleine grüne Agave",
    "green-small-grass" => "Grünes kurzes Gras",
    "gun-turret" => "Geschützturm",
    "inserter" => "Greifarm",
    "iron-chest" => "Eisenkiste",
    "iron-ore" => "Eisenerz",
    "item-on-ground" => "Gegenstand auf dem Boden",
    "item-request-proxy" => "Gegenstandsanforderungsproxy",
    "lab" => "Labor",
    "land-mine" => "Landmine",
    "laser-turret" => "Laserturm",
    "loader" => "Belader",
    "logistic-chest-active-provider" => "Aktive Anbieterkiste",
    "logistic-chest-passive-provider" => "Passive Anbieterkiste",
    "logistic-chest-requester" => "Anforderungskiste",
    "logistic-chest-storage" => "Lagerkiste",
    "logistic-robot" => "Logistikroboter",
    "long-handed-inserter" => "Langer Greifarm",
    "market" => "Markt",
    "medium-biter" => "Mittelgroßer Beißer",
    "medium-biter-corpse" => "Toter mittelgroßer Beißer",
    "medium-electric-pole" => "Mittelgroßer Strommast",
    "medium-remnants" => "Mittelgroße Überreste",
    "medium-ship-wreck" => "Mittelgroßes Schiffswrack",
    "medium-spitter" => "Mittelgroßer Speier",
    "medium-spitter-corpse" => "Toter mittelgroßer Speier",
    "medium-worm-corpse" => "Toter mittelgroßer Wurm",
    "medium-worm-turret" => "Mittelgroßer Wurm",
    "offshore-pump" => "Offshore-Pumpe",
    "oil-refinery" => "Ölraffinerie",
    "orange-coral-mini" => "Kleine orange Koralle",
    "pipe" => "Rohr",
    "pipe-to-ground" => "Unterirdisches Rohr",
    "player" => "Spieler",
    "player-port" => "Spawnpunkt",
    "power-switch" => "Stromschalter",
    "pumpjack" => "Rohöl-Förderpumpe",
    "radar" => "Radar",
    "rail-chain-signal" => "Zugvorsignal",
    "rail-signal" => "Zugsignal",
    "red-asterisk" => "Roter Stern",
    "roboport" => "Roboterhangar",
    "rocket" => "Rakete",
    "rocket-silo" => "Raketensilo",
    "rocket-turret" => "Raketenturm",
    "root-A" => "Kleine Wurzel",
    "root-B" => "Große Wurzel",
    "small-biter" => "Kleiner Beißer",
    "small-biter-corpse" => "Toter kleiner Beißer",
    "small-electric-pole" => "Kleiner Strommast",
    "small-lamp" => "Lampe",
    "small-pump" => "Kleine Pumpe",
    "small-remnants" => "Kleine Überreste",
    "small-rock" => "Kleiner Fels",
    "small-scorchmark" => "Kleine Brandspuren",
    "small-ship-wreck" => "Kleines Schiffswrack",
    "small-ship-wreck-grass" => "Gras unter dem kleinen Schiffswrack",
    "small-spitter" => "Kleiner Speier",
    "small-spitter-corpse" => "Toter kleiner Speier",
    "small-worm-corpse" => "Toter kleiner Wurm",
    "small-worm-turret" => "Kleiner Wurm",
    "solar-panel" => "Solarpanel",
    "space-module-wreck" => "Wrack einer Rettungskapsel",
    "spitter-spawner" => "Speiernest",
    "spitter-spawner-corpse" => "Speiernest-Überreste",
    "splitter" => "Teilerfließband",
    "stack-filter-inserter" => "Filternder Greifarm",
    "stack-inserter" => "Greifarm",
    "steam-engine" => "Dampfmaschine",
    "steel-chest" => "Stahlkiste",
    "steel-furnace" => "Hochofen",
    "stone" => "Stein",
    "stone-furnace" => "Schmelzofen",
    "stone-rock" => "Steinfelsen",
    "stone-wall" => "Steinwand",
    "storage-tank" => "Lagertank",
    "straight-rail" => "Gerade Schiene",
    "straight-rail-remnants" => "Überreste einer geraden Schiene",
    "substation" => "Umspannwerk",
    "tank" => "Panzer",
    "train-stop" => "Zughaltestelle",
    "transport-belt" => "Fließband",
    "tree-01" => "Baum 1",
    "tree-01-stump" => "Baumstumpf 1",
    "tree-02" => "Baum 2",
    "tree-02-red" => "Baum 2 rot",
    "tree-02-stump" => "Baumstumpf 2",
    "tree-03" => "Baum 3",
    "tree-03-stump" => "Baumstumpf 3",
    "tree-04" => "Baum 4",
    "tree-04-stump" => "Baumstumpf 4",
    "tree-05" => "Baum 5",
    "tree-05-stump" => "Baumstumpf 5",
    "tree-06" => "Baum 6",
    "tree-06-brown" => "Baum 6 braun",
    "tree-06-stump" => "Baumstumpf 6",
    "tree-07" => "Baum 7",
    "tree-07-stump" => "Baumstumpf 7",
    "tree-08" => "Baum 8",
    "tree-08-brown" => "Baum 8 braun",
    "tree-08-red" => "Baum 8 rot",
    "tree-08-stump" => "Baumstumpf 8",
    "tree-09" => "Baum 9",
    "tree-09-brown" => "Baum 9 braun",
    "tree-09-red" => "Baum 9 rot",
    "tree-09-stump" => "Baumstumpf 9",
    "underground-belt" => "Untergrund-Fließband",
    "wall-remnants" => "Mauerüberreste",
    "wooden-chest" => "Holzkiste",
    "heavy-oil" => "Schweröl",
    "light-oil" => "Leichtöl",
    "lubricant" => "Schmiermittel",
    "petroleum-gas" => "Petroleum",
    "sulfuric-acid" => "Schwefelsäure",
    "water" => "Wasser",
    "battery-equipment" => "Batterie",
    "battery-mk2-equipment" => "Batterie MK2",
    "discharge-defense-equipment" => "Elektroschockverteidigung",
    "energy-shield-equipment" => "Energieschild",
    "energy-shield-mk2-equipment" => "Energieschild MK2",
    "exoskeleton-equipment" => "Exoskelett",
    "fusion-reactor-equipment" => "Tragbarer Fusionsreaktor",
    "night-vision-equipment" => "Nachtsichtgerät",
    "personal-laser-defense-equipment" => "Persönliche Laserverteidigung",
    "personal-roboport-equipment" => "Persönlicher Roboterhangar",
    "solar-panel-equipment" => "Tragbares Solarpanel",
    "advanced-circuit" => "Erweiterter elektronischer Schaltkreis",
    "alien-artifact" => "Alien-Artefakt",
    "alien-science-pack" => "Alien-Wissenschaftspaket",
    "battery" => "Batterie",
    "blueprint" => "Blaupause",
    "blueprint-book" => "Blaupausen-Buch",
    "cannon-shell" => "Kanonengeschoss",
    "cluster-grenade" => "Splittergranate",
    "coin" => "Münze",
    "combat-shotgun" => "Kampfschrotflinte",
    "computer" => "Computer",
    "concrete" => "Beton",
    "copper-plate" => "Kupferplatte",
    "crude-oil-barrel" => "Rohölfass",
    "deconstruction-planner" => "Abrissplaner",
    "defender-capsule" => "Verteidiger-Kapsel",
    "destroyer-capsule" => "Zerstörer-Kapsel",
    "discharge-defense-remote" => "Fernbedienung für die Elektroschockverteidigung",
    "distractor-capsule" => "Ablenker-Kapsel",
    "effectivity-module" => "Effizienz-Modul",
    "effectivity-module-2" => "Effizienz-Modul 2",
    "effectivity-module-3" => "Effizienz-Modul 3",
    "electric-engine-unit" => "Elektromotorenbauteil",
    "electronic-circuit" => "Elektronischer Schaltkreis",
    "empty-barrel" => "Leeres Fass",
    "engine-unit" => "Verbrennungsmotorenbestandteil",
    "explosive-cannon-shell" => "Explosives Kanonengeschoss",
    "explosive-rocket" => "Explosive Rakete",
    "explosives" => "Sprengstoff",
    "firearm-magazine" => "Handfeuerwaffen Magazin",
    "flame-thrower" => "Flammenwerfer",
    "flame-thrower-ammo" => "Flammenwerferbrennstoff",
    "flying-robot-frame" => "Flugrobotergestell",
    "green-wire" => "Grünes Signalkabel",
    "grenade" => "Granate",
    "hazard-concrete" => "Gefahrenbereich",
    "heavy-armor" => "Schwere Rüstung",
    "iron-axe" => "Eisenspitzhacke",
    "iron-gear-wheel" => "Eisenzahnrad",
    "iron-plate" => "Eisenplatte",
    "iron-stick" => "Eisenstange",
    "landfill" => "Mülldeponie",
    "light-armor" => "Leichte Rüstung",
    "low-density-structure" => "Baumaterial mit geringer Dichte",
    "modular-armor" => "Modulare Rüstung",
    "piercing-rounds-magazine" => "Panzerbrechende Munition",
    "piercing-shotgun-shell" => "Panzerbrechende Schrotpatrone",
    "pistol" => "Pistole",
    "plastic-bar" => "Kunststoffstange",
    "poison-capsule" => "Gift-Kapsel",
    "power-armor" => "Kampfanzug",
    "power-armor-mk2" => "Kampfanzug MK2",
    "processing-unit" => "Prozessoreinheit",
    "productivity-module" => "Produktivitätsmodul 1",
    "productivity-module-2" => "Produktivitätsmodul 2",
    "productivity-module-3" => "Produktivitätsmodul 3",
    "rail" => "Schiene",
    "rail-planner" => "Gleisplaner",
    "railgun" => "Railgun",
    "railgun-dart" => "Railgun Projektil",
    "raw-fish" => "Roher Fisch",
    "raw-wood" => "Baumstämme",
    "red-wire" => "Rotes Signalkabel",
    "repair-pack" => "Reparaturkit",
    "rocket-control-unit" => "Raketensteuergerät",
    "rocket-fuel" => "Raketentreibstoff",
    "rocket-launcher" => "Raketenwerfer",
    "rocket-part" => "Raketenbauteile",
    "satellite" => "Satellit",
    "science-pack-1" => "Wissenschaftspaket 1",
    "science-pack-2" => "Wissenschaftspaket 2",
    "science-pack-3" => "Wissenschaftspaket 3",
    "shotgun" => "Schrotflinte",
    "shotgun-shell" => "Schrotpatrone",
    "slowdown-capsule" => "Verlangsamungs-Kapsel",
    "small-plane" => "Kleines Flugzeug",
    "solid-fuel" => "Festbrennstoff",
    "speed-module" => "Geschwindigkeitsmodul 1",
    "speed-module-2" => "Geschwindigkeitsmodul 2",
    "speed-module-3" => "Geschwindigkeitsmodul 3",
    "steel-axe" => "Stahlspitzhacke",
    "steel-plate" => "Stahlträger",
    "stone-brick" => "Ziegelstein",
    "stone-path" => "Steinweg",
    "submachine-gun" => "Maschinengewehr",
    "sulfur" => "Schwefel",
    "tank-cannon" => "Panzerkanone",
    "tank-machine-gun" => "MG-Fahrzeuglafette",
    "vehicle-machine-gun" => "Fahrzeug-Maschinengewehr",
    "wood" => "Holzbohlen",
);

function getItemName($item)
{
    global $names;
    if (isset($names[$item])) {
        return $names[$item];
    }
    return 'NOT TRANSLATED: ' . $item;
}