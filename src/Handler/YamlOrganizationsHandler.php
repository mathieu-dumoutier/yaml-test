<?php

namespace App\Handler;

use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;

class YamlOrganizationsHandler
{
    const FILE = 'organizations.yaml';

    /**
     * @var array
     */
    private $organizations;

    /**
     * @var string
     */
    private $filepath;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(string $projectDir, LoggerInterface $logger)
    {
        $this->filepath = $projectDir.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.self::FILE;
        $this->organizations = $this->transform(Yaml::parseFile($this->filepath));
        $this->logger = $logger;
    }

    public function write(array $organization, string $name = null): bool
    {
        $this->organizations[$name ?? $organization['name']] = $organization;
        return $this->save();
    }

    public function delete(string $name): bool
    {
        if (false === isset($this->organizations[$name])) {
            return false;
        }

        unset($this->organizations[$name]);
        return $this->save();
    }

    public function findAll(): array
    {
        return $this->organizations;
    }

    public function findByName(string $name): array
    {
        return $this->organizations[$name] ?? null;
    }

    private function transform(array $data): array
    {
        $organizations = [];

        foreach ($data['organizations'] as $organization) {
            $organizations[$organization['name']] = $organization;
        }

        return $organizations;
    }

    private function save(): bool
    {
        try {
            $array = ['organizations' => []];

            foreach ($this->organizations as $organization) {
                $array['organizations'][] = $organization;
            }

            $yaml = Yaml::dump($array);

            file_put_contents($this->filepath, $yaml);

            return true;
        } catch (\Throwable $throwable) {
            $this->logger->critical($throwable->getMessage());

            return false;
        }
    }
}