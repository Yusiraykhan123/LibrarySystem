USE LibrarySystem;
GO

-- 1. Books currently on loan, with the borrowing member and due date.
SELECT
    l.LoanID,
    b.Title,
    CONCAT(m.FirstName, N' ', m.LastName) AS MemberName,
    l.LoanDate,
    l.DueDate
FROM dbo.Loans AS l
INNER JOIN dbo.Books AS b ON b.BookID = l.BookID
INNER JOIN dbo.Members AS m ON m.MemberID = l.MemberID
WHERE l.ReturnedAt IS NULL
ORDER BY l.DueDate;
GO

-- 2. Overdue, unreturned books as of today.
SELECT
    l.LoanID,
    b.Title,
    CONCAT(m.FirstName, N' ', m.LastName) AS MemberName,
    m.Email,
    l.DueDate,
    DATEDIFF(day, l.DueDate, CONVERT(date, GETDATE())) AS DaysOverdue
FROM dbo.Loans AS l
INNER JOIN dbo.Books AS b ON b.BookID = l.BookID
INNER JOIN dbo.Members AS m ON m.MemberID = l.MemberID
WHERE l.ReturnedAt IS NULL
  AND l.DueDate < CONVERT(date, GETDATE())
ORDER BY l.DueDate;
GO

-- 3. All loans and fines for a member, including loans without a fine.
DECLARE @MemberID INT = 1;

SELECT
    l.LoanID,
    b.Title,
    l.LoanDate,
    l.DueDate,
    l.ReturnedAt,
    f.Amount AS FineAmount,
    f.PaidAt
FROM dbo.Loans AS l
INNER JOIN dbo.Books AS b ON b.BookID = l.BookID
LEFT JOIN dbo.Fines AS f ON f.LoanID = l.LoanID
WHERE l.MemberID = @MemberID
ORDER BY l.LoanDate DESC;
GO

-- 4. Every book and its total number of loans, including never-loaned books.
SELECT
    b.BookID,
    b.Title,
    COUNT(l.LoanID) AS TotalLoans
FROM dbo.Books AS b
LEFT JOIN dbo.Loans AS l ON l.BookID = b.BookID
GROUP BY b.BookID, b.Title
ORDER BY TotalLoans DESC, b.Title;
GO

-- 5. Outstanding fines by member.
SELECT
    m.MemberID,
    CONCAT(m.FirstName, N' ', m.LastName) AS MemberName,
    SUM(f.Amount) AS OutstandingBalance
FROM dbo.Fines AS f
INNER JOIN dbo.Loans AS l ON l.LoanID = f.LoanID
INNER JOIN dbo.Members AS m ON m.MemberID = l.MemberID
WHERE f.PaidAt IS NULL
GROUP BY m.MemberID, m.FirstName, m.LastName
ORDER BY OutstandingBalance DESC;
GO

-- 6. Loans due in the next seven days, including those due today.
SELECT
    l.LoanID,
    b.Title,
    CONCAT(m.FirstName, N' ', m.LastName) AS MemberName,
    l.DueDate
FROM dbo.Loans AS l
INNER JOIN dbo.Books AS b ON b.BookID = l.BookID
INNER JOIN dbo.Members AS m ON m.MemberID = l.MemberID
WHERE l.ReturnedAt IS NULL
  AND l.DueDate >= CONVERT(date, GETDATE())
  AND l.DueDate < DATEADD(day, 7, CONVERT(date, GETDATE()))
ORDER BY l.DueDate;
GO
