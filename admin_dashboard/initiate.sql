-- Create certificates table
CREATE TABLE certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_id VARCHAR(50) NOT NULL UNIQUE,
    recipient_name VARCHAR(100) NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    issue_date DATE NOT NULL,
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create index for faster lookups
CREATE INDEX idx_certificate_id ON certificates(certificate_id);

-- Insert sample data for testing
INSERT INTO certificates (certificate_id, recipient_name, course_name, issue_date, expiry_date)
VALUES 
('CERT-2023-001', 'John Doe', 'Web Development Fundamentals', '2023-01-15', '2026-01-15'),
('CERT-2023-002', 'Jane Smith', 'Advanced JavaScript', '2023-02-20', '2026-02-20'),
('CERT-2023-003', 'Michael Johnson', 'UI/UX Design Principles', '2023-03-10', '2026-03-10');