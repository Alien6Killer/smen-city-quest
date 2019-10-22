<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuestionRepository")
 */
class Question
{
    const TYPE_QUESTION = 0;
    const TYPE_HELP = 1;
    const TYPE_SKIP = 2;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $answer;

    /**
     * @var integer
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $type;

    /**
     * @ORM\Column(type="text")
     */
    private $next_question;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Question", cascade={"persist", "remove"})
     */
    private $prevQuestion;

    public function __toString(): string
    {
        return (string)$this->answer ?? 'new';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function getNextQuestion(): ?string
    {
        return $this->next_question;
    }

    public function setNextQuestion(string $next_question): self
    {
        $this->next_question = $next_question;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getPrevQuestion(): ?self
    {
        return $this->prevQuestion;
    }

    public function setPrevQuestion(?self $prevQuestion): self
    {
        $this->prevQuestion = $prevQuestion;

        return $this;
    }
}
