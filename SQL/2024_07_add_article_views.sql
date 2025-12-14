-- Adds a view counter to articles and seeds default values for existing rows
ALTER TABLE articles
    ADD COLUMN IF NOT EXISTS view_count INT UNSIGNED NOT NULL DEFAULT 0;

-- Initialize null values if the column already existed without defaults
UPDATE articles SET view_count = 0 WHERE view_count IS NULL;

-- Optional helper view to inspect popularity
CREATE OR REPLACE VIEW article_popularity AS
SELECT a.id_article,
       a.titre,
       a.view_count,
       SUM(CASE WHEN r.reaction = 'like' THEN 1 ELSE 0 END) AS likes,
       SUM(CASE WHEN r.reaction = 'dislike' THEN 1 ELSE 0 END) AS dislikes
FROM articles a
LEFT JOIN reactions r ON r.id_article = a.id_article
GROUP BY a.id_article, a.titre, a.view_count
ORDER BY view_count DESC, likes DESC;
