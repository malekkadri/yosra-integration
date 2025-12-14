CREATE TABLE IF NOT EXISTS categories (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom_categorie VARCHAR(150) NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS articles (
    id_article INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    id_categorie INT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    view_count INT UNSIGNED NOT NULL DEFAULT 0,
    author_id INT DEFAULT NULL,
    CONSTRAINT fk_article_categorie FOREIGN KEY (id_categorie) REFERENCES categories(id_categorie) ON DELETE CASCADE,
    CONSTRAINT fk_article_user FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS comment_articles (
    id_comment INT AUTO_INCREMENT PRIMARY KEY,
    id_article INT NOT NULL,
    id_user INT NOT NULL,
    contenu TEXT NOT NULL,
    date_comment DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comment_article FOREIGN KEY (id_article) REFERENCES articles(id_article) ON DELETE CASCADE,
    CONSTRAINT fk_comment_user FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reactions (
    id_reaction INT AUTO_INCREMENT PRIMARY KEY,
    id_article INT NOT NULL,
    user_id INT NOT NULL,
    reaction ENUM('like','dislike') NOT NULL,
    date_reaction DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reaction_article FOREIGN KEY (id_article) REFERENCES articles(id_article) ON DELETE CASCADE,
    CONSTRAINT fk_reaction_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT uq_user_reaction UNIQUE (id_article, user_id)
);
