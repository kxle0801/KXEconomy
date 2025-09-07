-- #!mysql

-- #{ table
-- #{ economy
CREATE TABLE IF NOT EXISTS economy (
    uniqueid VARCHAR(48) PRIMARY KEY NOT NULL,
    username VARCHAR(18),
    gold INTEGER(48) DEFAULT 1000,
    gems INTEGER(48) DEFAULT 0,
    tokens INTEGER(48) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- #}

-- #{ economy_transactions
CREATE TABLE IF NOT EXISTS economy_transactions (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    uniqueid VARCHAR(48) NOT NULL,
    type VARCHAR(20) NOT NULL,
    currency VARCHAR(20) NOT NULL,
    amount INTEGER(48) NOT NULL,
    reason TEXT,
    timestamp INTEGER(48) NOT NULL,
    INDEX idx_uniqueid (uniqueid),
    INDEX idx_timestamp (timestamp),
    INDEX idx_type (type),
    INDEX idx_currency (currency)
);
-- #}
-- #}

-- #{ economy
-- #{ create
-- #  :uniqueid string
-- #  :username string
-- #  :gold int
-- #  :gems int
-- #  :tokens int
INSERT IGNORE INTO economy (uniqueid, username, gold, gems, tokens)
VALUES (:uniqueid, :username, :gold, :gems, :tokens);
-- #}

-- #{ all
SELECT * FROM economy ORDER BY gold DESC;
-- #}

-- #{ get
-- #  :uniqueid string
SELECT * FROM economy WHERE uniqueid = :uniqueid;
-- #}

-- #{ update
-- #  :uniqueid string
-- #  :username string
-- #  :gold int
-- #  :gems int
-- #  :tokens int
UPDATE economy
SET username = :username,
    gold = :gold,
    gems = :gems,
    tokens = :tokens,
    updated_at = CURRENT_TIMESTAMP
WHERE uniqueid = :uniqueid;
-- #}

-- #{ delete
-- #  :uniqueid string
DELETE FROM economy WHERE uniqueid = :uniqueid;
-- #}

-- #{ top
-- #  :currency string
-- #  :limit int
SELECT * FROM economy 
ORDER BY 
  CASE 
    WHEN :currency = 'gold' THEN gold
    WHEN :currency = 'gems' THEN gems
    WHEN :currency = 'tokens' THEN tokens
    ELSE 0
  END DESC
LIMIT :limit;
-- #}

-- #{ log
-- #  :uniqueid string
-- #  :type string
-- #  :currency string
-- #  :amount int
-- #  :reason string
-- #  :timestamp int
INSERT INTO economy_transactions (uniqueid, type, currency, amount, reason, timestamp)
VALUES (:uniqueid, :type, :currency, :amount, :reason, :timestamp);
-- #}
-- #}

-- #{ transactions
-- #{ player
-- #  :uniqueid string
-- #  :limit int
SELECT * FROM economy_transactions 
WHERE uniqueid = :uniqueid 
ORDER BY timestamp DESC 
LIMIT :limit;
-- #}

-- #{ all
-- #  :limit int
SELECT * FROM economy_transactions 
ORDER BY timestamp DESC 
LIMIT :limit;
-- #}

-- #{ by_type
-- #  :type string
-- #  :limit int
SELECT * FROM economy_transactions 
WHERE type = :type 
ORDER BY timestamp DESC 
LIMIT :limit;
-- #}
-- #}