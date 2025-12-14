<?php
class Reaction {
    private int $idReaction;
    private int $idArticle;
    private string $reaction;
    private string $dateReaction;

    public function __construct(int $idArticle, string $reaction) {
        $this->idArticle = $idArticle;
        $this->reaction = $reaction;
        $this->dateReaction = date('Y-m-d H:i:s');
    }

    public function getIdArticle() { return $this->idArticle; }
    public function getReaction() { return $this->reaction; }
    public function getDateReaction() { return $this->dateReaction; }

    public function setIdArticle($idArticle) { $this->idArticle = $idArticle; }
    public function setReaction($reaction) { $this->reaction = $reaction; }
    public function setDateReaction($dateReaction) { $this->dateReaction = $dateReaction; }
}
?>
