ALTER TABLE user_episodes ADD COLUMN airdate VARCHAR(50), ADD COLUMN airtime VARCHAR(50);
IMPORT user_episodes
ALTER TABLE user_episodes DROP COLUMN airdate, DROP COLUMN airtime;
UPDATE user_episodes ue SET ue.user_show_id = (SELECT id FROM user_shows WHERE show_id=ue.show_id AND user_id=ue.user_id);

SET @ids = (SELECT ue.id FROM user_episodes ue 
LEFT JOIN episodes e ON e.id = ue.episode_id
WHERE e.id IS NULL);
DELETE FROM user_episodes WHERE id IN (@ids);

UPDATE fos_user SET calendar_show = NULL;
