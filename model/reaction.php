<?php
class Reaction {
    private int $idReaction;
    private int $idArticle;
    private int $userId;
    private string $reaction;
    private string $dateReaction;

    public function __construct(int $idArticle, int $userId, string $reaction) {
        $this->idArticle = $idArticle;
        $this->userId = $userId;
        $this->reaction = $reaction;
        $this->dateReaction = date('Y-m-d H:i:s');
    }

    public function getIdArticle() { return $this->idArticle; }
    public function getUserId() { return $this->userId; }
    public function getReaction() { return $this->reaction; }
    public function getDateReaction() { return $this->dateReaction; }

    public function setIdArticle($idArticle) { $this->idArticle = $idArticle; }
    public function setUserId($userId) { $this->userId = $userId; }
    public function setReaction($reaction) { $this->reaction = $reaction; }
    public function setDateReaction($dateReaction) { $this->dateReaction = $dateReaction; }
}
?>
