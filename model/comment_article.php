<?php
class CommentArticle {
    private int $idComment;
    private int $idArticle;
    private int $idUser;
    private string $contenu;
    private string $dateComment;

    public function __construct(int $idArticle, int $idUser, string $contenu, ?string $dateComment = null) {
        $this->idArticle = $idArticle;
        $this->idUser = $idUser;
        $this->contenu = $contenu;
        $this->dateComment = $dateComment ?? date('Y-m-d H:i:s');
    }

    public function getIdArticle(): int { return $this->idArticle; }
    public function getIdUser(): int { return $this->idUser; }
    public function getContenu(): string { return $this->contenu; }
    public function getDateComment(): string { return $this->dateComment; }

    public function setIdArticle(int $idArticle): void { $this->idArticle = $idArticle; }
    public function setIdUser(int $idUser): void { $this->idUser = $idUser; }
    public function setContenu(string $contenu): void { $this->contenu = $contenu; }
    public function setDateComment(string $dateComment): void { $this->dateComment = $dateComment; }
}
?>
