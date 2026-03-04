<?php

namespace Soukar\QAlert\Entities;

class EventParser {

    public function __construct(
        private $event
    ) {}

    public function getMessage(): string {
        return sprintf(
            "⚠️ Job Failed Alert\n" .
            "Job: %s\n" .
            "Connection: %s\n" .
            "Queue: %s\n" .
            "Error: %s\n" .
            "File: %s\n" .
            "Line: %d\n" .
            "Time: %s",
            $this->getJobName(),
            $this->getConnectionName(),
            $this->getQueue(),
            $this->getExceptionMessage(),
            $this->getFile(),
            $this->getLine(),
            $this->getTimestamp()
        );
    }

    public function getJobName(): string {
        return $this->event->job->resolveName();
    }

    public function getQueue(): string {
        return $this->event->job->getQueue();
    }

    public function getConnectionName(): string {
        return $this->event->connectionName;
    }

    public function getExceptionMessage(): string {
        return $this->event->exception->getMessage();
    }

    public function getFile(): string {
        return $this->event->exception->getFile();
    }

    public function getLine(): int {
        return $this->event->exception->getLine();
    }

    public function getTimestamp(): string {
        return now()->toDateTimeString();
    }

    public function getRawBody(): string {
        return $this->event->job->getRawBody();
    }

}