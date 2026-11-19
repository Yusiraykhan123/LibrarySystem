IF DB_ID(N'LibrarySystem') IS NULL
BEGIN
    CREATE DATABASE LibrarySystem;
END;
GO

USE LibrarySystem;
GO

CREATE TABLE dbo.Books
(
    BookID INT IDENTITY(1,1) CONSTRAINT PK_Books PRIMARY KEY,
    ISBN NVARCHAR(20) NOT NULL CONSTRAINT UQ_Books_ISBN UNIQUE,
    Title NVARCHAR(200) NOT NULL,
    Author NVARCHAR(150) NOT NULL,
    PublishedYear SMALLINT NULL,
    TotalCopies INT NOT NULL CONSTRAINT DF_Books_TotalCopies DEFAULT (1),
    CONSTRAINT CK_Books_TotalCopies CHECK (TotalCopies >= 0),
    CONSTRAINT CK_Books_PublishedYear CHECK (PublishedYear IS NULL OR PublishedYear BETWEEN 1 AND 9999)
);
GO

CREATE TABLE dbo.Members
(
    MemberID INT IDENTITY(1,1) CONSTRAINT PK_Members PRIMARY KEY,
    FirstName NVARCHAR(80) NOT NULL,
    LastName NVARCHAR(80) NOT NULL,
    Email NVARCHAR(254) NOT NULL CONSTRAINT UQ_Members_Email UNIQUE,
    JoinedAt DATE NOT NULL CONSTRAINT DF_Members_JoinedAt DEFAULT (CONVERT(date, GETDATE())),
    IsActive BIT NOT NULL CONSTRAINT DF_Members_IsActive DEFAULT (1)
);
GO

CREATE TABLE dbo.Loans
(
    LoanID INT IDENTITY(1,1) CONSTRAINT PK_Loans PRIMARY KEY,
    BookID INT NOT NULL,
    MemberID INT NOT NULL,
    LoanDate DATE NOT NULL CONSTRAINT DF_Loans_LoanDate DEFAULT (CONVERT(date, GETDATE())),
    DueDate DATE NOT NULL,
    ReturnedAt DATE NULL,
    CONSTRAINT FK_Loans_Books FOREIGN KEY (BookID) REFERENCES dbo.Books (BookID),
    CONSTRAINT FK_Loans_Members FOREIGN KEY (MemberID) REFERENCES dbo.Members (MemberID),
    CONSTRAINT CK_Loans_DueDate CHECK (DueDate >= LoanDate),
    CONSTRAINT CK_Loans_ReturnedAt CHECK (ReturnedAt IS NULL OR ReturnedAt >= LoanDate)
);
GO

CREATE TABLE dbo.Fines
(
    FineID INT IDENTITY(1,1) CONSTRAINT PK_Fines PRIMARY KEY,
    LoanID INT NOT NULL,
    Amount DECIMAL(8,2) NOT NULL,
    IssuedAt DATE NOT NULL CONSTRAINT DF_Fines_IssuedAt DEFAULT (CONVERT(date, GETDATE())),
    PaidAt DATE NULL,
    CONSTRAINT FK_Fines_Loans FOREIGN KEY (LoanID) REFERENCES dbo.Loans (LoanID),
    CONSTRAINT UQ_Fines_LoanID UNIQUE (LoanID),
    CONSTRAINT CK_Fines_Amount CHECK (Amount > 0),
    CONSTRAINT CK_Fines_PaidAt CHECK (PaidAt IS NULL OR PaidAt >= IssuedAt)
);
GO

CREATE INDEX IX_Loans_DueDate_ReturnedAt ON dbo.Loans (DueDate, ReturnedAt);
CREATE INDEX IX_Loans_MemberID_LoanDate ON dbo.Loans (MemberID, LoanDate);
CREATE INDEX IX_Loans_BookID_LoanDate ON dbo.Loans (BookID, LoanDate);
CREATE INDEX IX_Fines_PaidAt ON dbo.Fines (PaidAt);
GO
