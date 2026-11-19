USE artcraft1db;

ALTER TABLE artworks
  ADD COLUMN views INT NOT NULL DEFAULT 0,
  ADD COLUMN reject_reason VARCHAR(255) NULL;

ALTER TABLE events
  ADD COLUMN max_participants INT NOT NULL DEFAULT 0;
