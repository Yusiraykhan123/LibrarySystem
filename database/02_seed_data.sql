USE LibrarySystem;
GO

-- Run once after creating the schema. Sample dates stay relative to today
-- so the due-date queries always include useful examples.
INSERT INTO dbo.Books (ISBN, Title, Author, PublishedYear, TotalCopies)
VALUES
    (N'978-0132350884', N'Clean Code', N'Robert C. Martin', 2008, 3),
    (N'978-0201616224', N'The Pragmatic Programmer', N'Andrew Hunt and David Thomas', 1999, 2),
    (N'978-0596007126', N'Head First Design Patterns', N'Eric Freeman and Elisabeth Robson', 2004, 2),
    (N'978-1492052203', N'Fluent Python', N'Luciano Ramalho', 2022, 1),
    (N'978-0135957059', N'The C Programming Language', N'Brian W. Kernighan and Dennis M. Ritchie', 1988, 1);
GO

INSERT INTO dbo.Members (FirstName, LastName, Email, JoinedAt)
VALUES
    (N'Avery', N'Johnson', N'avery.johnson@example.com', DATEADD(day, -180, CONVERT(date, GETDATE()))),
    (N'Jordan', N'Lee', N'jordan.lee@example.com', DATEADD(day, -90, CONVERT(date, GETDATE()))),
    (N'Morgan', N'Patel', N'morgan.patel@example.com', DATEADD(day, -30, CONVERT(date, GETDATE()))),
    (N'Taylor', N'Garcia', N'taylor.garcia@example.com', DATEADD(day, -10, CONVERT(date, GETDATE())));
GO

INSERT INTO dbo.Loans (BookID, MemberID, LoanDate, DueDate, ReturnedAt)
VALUES
    (1, 1, DATEADD(day, -30, CONVERT(date, GETDATE())), DATEADD(day, -16, CONVERT(date, GETDATE())), NULL),
    (2, 2, DATEADD(day, -10, CONVERT(date, GETDATE())), DATEADD(day, 4, CONVERT(date, GETDATE())), NULL),
    (3, 1, DATEADD(day, -45, CONVERT(date, GETDATE())), DATEADD(day, -31, CONVERT(date, GETDATE())), DATEADD(day, -32, CONVERT(date, GETDATE()))),
    (4, 3, DATEADD(day, -2, CONVERT(date, GETDATE())), DATEADD(day, 12, CONVERT(date, GETDATE())), NULL);
GO

INSERT INTO dbo.Fines (LoanID, Amount, IssuedAt, PaidAt)
VALUES
    (1, 8.00, DATEADD(day, -5, CONVERT(date, GETDATE())), NULL),
    (3, 2.00, DATEADD(day, -32, CONVERT(date, GETDATE())), DATEADD(day, -30, CONVERT(date, GETDATE())));
GO
