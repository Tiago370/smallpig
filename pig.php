<?php
interface ContainerInterface {
    public function setName($name);
    public function getName(): string;
    public function getBalance(): int;
    public function getData(): array;
}
class Pig implements ContainerInterface {
    private $name;
    private $balance;
    private $goal;
    function __construct($name) {
        $this->name = $name;
        $this->balance = 0;
        $this->goal = NULL;
    }
    public function setName($name) {
        $this->name = $name;
    }
    public function getName(): string {
        return $this->name;
    }
    public function getBalance(): int {
        return $this->balance;
    }
    public function deposit($amount) {
        $this->balance += $amount;
    }
    public function withdraw($amount): bool {
        if($this->balance >= $amount) {
            $this->balance -= $amount;
            return true;
        }
        return false;
    }
    public function setGoal($goal) {
        $this->goal = $goal;
    }
    public function getGoal(): int {
        return $this->goal;
    }
    public function getPercentage(): int {
        if($this->goal == NULL) {
            return 100;
        }
        return ($this->balance / $this->goal) * 100;
    }
    public function getData(): array {
        return [
            'name' => $this->name,
            'balance' => $this->balance,
            'goal' => $this->goal
        ];
    }
}
class Folder implements ContainerInterface {
    private $name;
    private $pigs = [];
    private $folders = [];
    function __construct($name) {
        $this->name = $name;
    }
    public function setName($name) {
        $this->name = $name;
    }
    public function getName(): string {
        return $this->name;
    }
    public function getBalance(): int {
        $balance = 0;
        foreach($this->pigs as $pig) {
            $balance += $pig->getBalance();
        }
        foreach($this->folders as $folder) {
            $balance += $folder->getBalance();
        }
        return $balance;
    }
    public function addPig(Pig $pig) {
        $this->pigs[] = $pig;
    }
    public function addFolder(Folder $folder) {
        $this->folders[] = $folder;
    }
    public function getPigsWithGoal(): array {
        $pigs = [];
        foreach($this->pigs as $pig) {
            if($pig->getGoal() != NULL) {
                $pigs[] = $pig;
            }
        }
        foreach($this->folders as $folder) {
            $pigs = array_merge($pigs, $folder->getPigsWithGoal());
        }
        return $pigs;
    }
    public function getTotalGoal(): int {
        $goal = 0;
        foreach($this->pigs as $pig) {
            if ($pig->getGoal() != NULL) {
                $goal += $pig->getGoal();
            }
        }
        foreach($this->folders as $folder) {
            $goal += $folder->getTotalGoal();
        }
        return $goal;
    }
    public function getTotalPercentage(): int {
        $totalBalance = $this->getBalance();
        $totalGoal = $this->getTotalGoal();
        if($totalGoal == 0) {
            return 100;
        }
        return ($totalBalance / $totalGoal) * 100;
    }
    public function addPigAtFolder(Pig $pig, array $path): bool {
        $firstFolder = array_shift($path);
        foreach($this->folders as $folder) {
            if($folder->getName() == $firstFolder) {
                if(count($path) == 0) {
                    $folder->addPig($pig);
                    echo "Added pig " . $pig->getName() . " to folder " . $folder->getName() . "\n";
                    return true;
                }
                return $folder->addPigAtFolder($pig, $path);
            }
        }
        return false;
    }
    public function addFolderAtFolder(Folder $folder, array $path): bool {
        $firstFolder = array_shift($path);
        foreach($this->folders as $subFolder) {
            if($subFolder->getName() == $firstFolder) {
                if(count($path) == 0) {
                    $subFolder->addFolder($folder);
                    echo "Adicionando pasta " . $folder->getName() . " na pasta " . $subFolder->getName() . "\n";
                    return true;
                }
                return $subFolder->addFolderAtFolder($folder, $path);
            }
        }
        return false;
    }
    public function getData(): array {
        $data = [
            'name' => $this->name,
            'pigs' => [],
            'folders' => []
        ];
        foreach($this->pigs as $pig) {
            $data['pigs'][] = $pig->getData();
        }
        foreach($this->folders as $folder) {
            $data['folders'][] = $folder->getData();
        }
        return $data;
    }
    public function &getFolderAtPath(array $path): Folder {
        $firstFolder = array_shift($path);
        foreach($this->folders as $folder) {
            if($folder->getName() == $firstFolder) {
                if(count($path) == 0) {
                    return $folder;
                }
                return $folder->getFolderAtPath($path);
            }
        }
        return $this;
    }
}
class Source {
    private $name;
    function __construct($name) {
        $this->name = $name;
        $this->balance = 0;
    }
    public function setName($name) {
        $this->name = $name;
    }
    public function getName(): string {
        return $this->name;
    }
    public function getBalance(): int {
        return $this->balance;
    }
    public function deposit($amount) {
        $this->balance += $amount;
    }
    public function withdraw($amount): bool {
        if($this->balance >= $amount) {
            $this->balance -= $amount;
            return true;
        }
        return false;
    }
    public function getData(): array {
        return [
            'name' => $this->name,
            'balance' => $this->balance
        ];
    }
}
class Wallet {
    private $name;
    private $sources = [];
    private $folder;
    private $path = [];
    function __construct($name) {
        $this->name = $name;
        $this->folder = new Folder('Main');
        $this->path[] = "Main";
    }
    public function setName($name) {
        $this->name = $name;
    }
    public function getName(): string {
        return $this->name;
    }
    public function getBalance(): int {
        return $this->folder->getBalance();
    }
    public function addPig(string $name, int $goal = NULL) {
        $currentFolder = &$this->folder->getFolderAtPath($this->path);
        if($currentFolder != NULL){
            $pig = new Pig($name);
            $pig->setGoal($goal);
            $currentFolder->addPig($pig);
        }
    }
    public function addFolder(string $name) {
        $currentFolder = &$this->folder->getFolderAtPath($this->path);
        if($currentFolder != NULL) {
            $this->folder->addFolder(new Folder($name));
        }
    }
    public function addSource(string $name, $amount) {
        $source = new Source($name);
        $source->deposit($amount);
        $this->sources[] = $source;
    }
    public function removeSource($name) {
        foreach($this->sources as $source) {
            if($source->getName() == $name) {
                $this->sources = array_filter($this->sources, function($source) use ($name) {
                    return $source->getName() != $name;
                });
                return true;
            }
        }
        return false;
    }
    public function getData(): array {
        $sources_data = [];
        foreach($this->sources as $source) {
            $sources_data[] = $source->getData();
        }
        $data = [
            'name' => $this->name,
            'folder' => $this->folder->getData(),
            'sources' => $sources_data
        ];
        return $data;
    }
    public function getNamesFoldersInCurrentFolder(): array {
        $names = [];
        $currentFolder = $this->folder->getFolderAtPath($this->path);
        foreach($currentFolder->getFolders() as $folder) {
            $names[] = $folder->getName();
        }
        return $names;
    }
    public function getNamesPigsInCurrentFolder(): array {
        $names = [];
        $currentFolder = $this->folder->getFolderAtPath($this->path);
        foreach($currentFolder->getPigs() as $pig) {
            $names[] = $pig->getName();
        }
        return $names;
    }
    public function goToFolder(string $name) {
        $this->path[] = $name;
    }
    public function exitTheFolder() {
        array_pop($this->path);
    }
}
$wallet = new Wallet('Wallet');
$wallet->addSource('Banco do Brasil', 1000);
$wallet->addSource('Banco do Bradesco', 2000);
$wallet->addSource('Banco Santander', 3000);

$wallet->addFolder('Gastos Fixos');
$wallet->goToFolder('Gastos Fixos');
$wallet->addPig('Aluguel');
echo json_encode($wallet->getData(), JSON_PRETTY_PRINT)."\n";
?>  