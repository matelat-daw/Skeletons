USE [master]
GO
/****** Object:  Database [NexusUsers]    Script Date: 28/06/2025 9:46:16 ******/
CREATE DATABASE [NexusUsers]
 CONTAINMENT = NONE
 ON  PRIMARY 
( NAME = N'NexusUsers', FILENAME = N'C:\Program Files\Microsoft SQL Server\MSSQL16.SPACE\MSSQL\DATA\NexusUsers.mdf' , SIZE = 8192KB , MAXSIZE = UNLIMITED, FILEGROWTH = 65536KB )
 LOG ON 
( NAME = N'NexusUsers_log', FILENAME = N'C:\Program Files\Microsoft SQL Server\MSSQL16.SPACE\MSSQL\DATA\NexusUsers_log.ldf' , SIZE = 8192KB , MAXSIZE = 2048GB , FILEGROWTH = 65536KB )
 WITH CATALOG_COLLATION = DATABASE_DEFAULT, LEDGER = OFF
GO
ALTER DATABASE [NexusUsers] SET COMPATIBILITY_LEVEL = 160
GO
IF (1 = FULLTEXTSERVICEPROPERTY('IsFullTextInstalled'))
begin
EXEC [NexusUsers].[dbo].[sp_fulltext_database] @action = 'enable'
end
GO
ALTER DATABASE [NexusUsers] SET ANSI_NULL_DEFAULT OFF 
GO
ALTER DATABASE [NexusUsers] SET ANSI_NULLS OFF 
GO
ALTER DATABASE [NexusUsers] SET ANSI_PADDING OFF 
GO
ALTER DATABASE [NexusUsers] SET ANSI_WARNINGS OFF 
GO
ALTER DATABASE [NexusUsers] SET ARITHABORT OFF 
GO
ALTER DATABASE [NexusUsers] SET AUTO_CLOSE ON 
GO
ALTER DATABASE [NexusUsers] SET AUTO_SHRINK OFF 
GO
ALTER DATABASE [NexusUsers] SET AUTO_UPDATE_STATISTICS ON 
GO
ALTER DATABASE [NexusUsers] SET CURSOR_CLOSE_ON_COMMIT OFF 
GO
ALTER DATABASE [NexusUsers] SET CURSOR_DEFAULT  GLOBAL 
GO
ALTER DATABASE [NexusUsers] SET CONCAT_NULL_YIELDS_NULL OFF 
GO
ALTER DATABASE [NexusUsers] SET NUMERIC_ROUNDABORT OFF 
GO
ALTER DATABASE [NexusUsers] SET QUOTED_IDENTIFIER OFF 
GO
ALTER DATABASE [NexusUsers] SET RECURSIVE_TRIGGERS OFF 
GO
ALTER DATABASE [NexusUsers] SET  ENABLE_BROKER 
GO
ALTER DATABASE [NexusUsers] SET AUTO_UPDATE_STATISTICS_ASYNC OFF 
GO
ALTER DATABASE [NexusUsers] SET DATE_CORRELATION_OPTIMIZATION OFF 
GO
ALTER DATABASE [NexusUsers] SET TRUSTWORTHY OFF 
GO
ALTER DATABASE [NexusUsers] SET ALLOW_SNAPSHOT_ISOLATION OFF 
GO
ALTER DATABASE [NexusUsers] SET PARAMETERIZATION SIMPLE 
GO
ALTER DATABASE [NexusUsers] SET READ_COMMITTED_SNAPSHOT ON 
GO
ALTER DATABASE [NexusUsers] SET HONOR_BROKER_PRIORITY OFF 
GO
ALTER DATABASE [NexusUsers] SET RECOVERY SIMPLE 
GO
ALTER DATABASE [NexusUsers] SET  MULTI_USER 
GO
ALTER DATABASE [NexusUsers] SET PAGE_VERIFY CHECKSUM  
GO
ALTER DATABASE [NexusUsers] SET DB_CHAINING OFF 
GO
ALTER DATABASE [NexusUsers] SET FILESTREAM( NON_TRANSACTED_ACCESS = OFF ) 
GO
ALTER DATABASE [NexusUsers] SET TARGET_RECOVERY_TIME = 60 SECONDS 
GO
ALTER DATABASE [NexusUsers] SET DELAYED_DURABILITY = DISABLED 
GO
ALTER DATABASE [NexusUsers] SET ACCELERATED_DATABASE_RECOVERY = OFF  
GO
ALTER DATABASE [NexusUsers] SET QUERY_STORE = ON
GO
ALTER DATABASE [NexusUsers] SET QUERY_STORE (OPERATION_MODE = READ_WRITE, CLEANUP_POLICY = (STALE_QUERY_THRESHOLD_DAYS = 30), DATA_FLUSH_INTERVAL_SECONDS = 900, INTERVAL_LENGTH_MINUTES = 60, MAX_STORAGE_SIZE_MB = 1000, QUERY_CAPTURE_MODE = AUTO, SIZE_BASED_CLEANUP_MODE = AUTO, MAX_PLANS_PER_QUERY = 200, WAIT_STATS_CAPTURE_MODE = ON)
GO
USE [NexusUsers]
GO
/****** Object:  User [NT AUTHORITY\SYSTEM]    Script Date: 28/06/2025 9:46:16 ******/
CREATE USER [NT AUTHORITY\SYSTEM] FOR LOGIN [NT AUTHORITY\SYSTEM] WITH DEFAULT_SCHEMA=[dbo]
GO
/****** Object:  DatabaseRole [SQLArcExtensionUserRole]    Script Date: 28/06/2025 9:46:16 ******/
CREATE ROLE [SQLArcExtensionUserRole]
GO
ALTER ROLE [SQLArcExtensionUserRole] ADD MEMBER [NT AUTHORITY\SYSTEM]
GO
/****** Object:  Table [dbo].[__EFMigrationsHistory]    Script Date: 28/06/2025 9:46:16 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[__EFMigrationsHistory](
	[MigrationId] [nvarchar](150) NOT NULL,
	[ProductVersion] [nvarchar](32) NOT NULL,
 CONSTRAINT [PK___EFMigrationsHistory] PRIMARY KEY CLUSTERED 
(
	[MigrationId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[AspNetRoleClaims]    Script Date: 28/06/2025 9:46:16 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[AspNetRoleClaims](
	[Id] [int] IDENTITY(1,1) NOT NULL,
	[RoleId] [nvarchar](450) NOT NULL,
	[ClaimType] [nvarchar](max) NULL,
	[ClaimValue] [nvarchar](max) NULL,
 CONSTRAINT [PK_AspNetRoleClaims] PRIMARY KEY CLUSTERED 
(
	[Id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[AspNetRoles]    Script Date: 28/06/2025 9:46:16 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[AspNetRoles](
	[Id] [nvarchar](450) NOT NULL,
	[Name] [varchar](255) NULL,
	[NormalizedName] [nvarchar](256) NULL,
	[ConcurrencyStamp] [nvarchar](max) NULL,
 CONSTRAINT [PK_AspNetRoles] PRIMARY KEY CLUSTERED 
(
	[Id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[AspNetUserClaims]    Script Date: 28/06/2025 9:46:16 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[AspNetUserClaims](
	[Id] [int] IDENTITY(1,1) NOT NULL,
	[UserId] [nvarchar](450) NOT NULL,
	[ClaimType] [nvarchar](max) NULL,
	[ClaimValue] [nvarchar](max) NULL,
 CONSTRAINT [PK_AspNetUserClaims] PRIMARY KEY CLUSTERED 
(
	[Id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[AspNetUserLogins]    Script Date: 28/06/2025 9:46:16 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[AspNetUserLogins](
	[LoginProvider] [nvarchar](450) NOT NULL,
	[ProviderKey] [nvarchar](450) NOT NULL,
	[ProviderDisplayName] [nvarchar](max) NULL,
	[UserId] [nvarchar](450) NOT NULL,
 CONSTRAINT [PK_AspNetUserLogins] PRIMARY KEY CLUSTERED 
(
	[LoginProvider] ASC,
	[ProviderKey] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[AspNetUserRoles]    Script Date: 28/06/2025 9:46:16 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[AspNetUserRoles](
	[UserId] [nvarchar](450) NOT NULL,
	[RoleId] [nvarchar](450) NOT NULL,
 CONSTRAINT [PK_AspNetUserRoles] PRIMARY KEY CLUSTERED 
(
	[UserId] ASC,
	[RoleId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[AspNetUsers]    Script Date: 28/06/2025 9:46:16 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[AspNetUsers](
	[Id] [nvarchar](450) NOT NULL,
	[Nick] [nvarchar](20) NOT NULL,
	[Name] [varchar](255) NULL,
	[Surname1] [varchar](255) NULL,
	[Surname2] [varchar](255) NULL,
	[Bday] [date] NOT NULL,
	[ProfileImage] [varchar](255) NULL,
	[About] [varchar](255) NULL,
	[UserLocation] [varchar](255) NULL,
	[PublicProfile] [bit] NOT NULL,
	[UserName] [varchar](255) NULL,
	[NormalizedUserName] [nvarchar](256) NULL,
	[Email] [varchar](255) NULL,
	[NormalizedEmail] [nvarchar](256) NULL,
	[EmailConfirmed] [bit] NOT NULL,
	[PasswordHash] [varchar](255) NULL,
	[SecurityStamp] [varchar](255) NULL,
	[ConcurrencyStamp] [varchar](255) NULL,
	[PhoneNumber] [varchar](255) NULL,
	[PhoneNumberConfirmed] [bit] NOT NULL,
	[TwoFactorEnabled] [bit] NOT NULL,
	[LockoutEnd] [date] NULL,
	[LockoutEnabled] [bit] NOT NULL,
	[AccessFailedCount] [int] NOT NULL,
 CONSTRAINT [PK_AspNetUsers] PRIMARY KEY CLUSTERED 
(
	[Id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[AspNetUserTokens]    Script Date: 28/06/2025 9:46:16 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[AspNetUserTokens](
	[UserId] [nvarchar](450) NOT NULL,
	[LoginProvider] [nvarchar](450) NOT NULL,
	[Name] [nvarchar](450) NOT NULL,
	[Value] [nvarchar](max) NULL,
 CONSTRAINT [PK_AspNetUserTokens] PRIMARY KEY CLUSTERED 
(
	[UserId] ASC,
	[LoginProvider] ASC,
	[Name] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Comments]    Script Date: 28/06/2025 9:46:16 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Comments](
	[Id] [int] IDENTITY(1,1) NOT NULL,
	[UserNick] [nvarchar](max) NULL,
	[Comment] [nvarchar](max) NULL,
	[UserId] [nvarchar](450) NULL,
	[ConstellationId] [int] NOT NULL,
	[ConstellationName] [nvarchar](max) NULL,
 CONSTRAINT [PK_Comments] PRIMARY KEY CLUSTERED 
(
	[Id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[EmailConfirmations]    Script Date: 28/06/2025 9:46:16 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[EmailConfirmations](
	[Id] [nvarchar](36) NOT NULL,
	[Token] [nvarchar](255) NOT NULL,
	[UserId] [nvarchar](450) NULL,
	[ExpiryDate] [datetime2](7) NULL,
PRIMARY KEY CLUSTERED 
(
	[Id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Favorites]    Script Date: 28/06/2025 9:46:16 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Favorites](
	[Id] [int] IDENTITY(1,1) NOT NULL,
	[UserId] [nvarchar](450) NULL,
	[ConstellationId] [int] NULL,
 CONSTRAINT [PK_Favorite] PRIMARY KEY CLUSTERED 
(
	[Id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
INSERT [dbo].[__EFMigrationsHistory] ([MigrationId], [ProductVersion]) VALUES (N'20250520213913_InitialUsers', N'8.0.16')
INSERT [dbo].[__EFMigrationsHistory] ([MigrationId], [ProductVersion]) VALUES (N'20250521193223_InitialFavorites', N'8.0.16')
GO
INSERT [dbo].[AspNetRoles] ([Id], [Name], [NormalizedName], [ConcurrencyStamp]) VALUES (N'0f7c852c-cdcd-4124-8d4b-479103876bd7', N'Admin', N'ADMIN', NULL)
INSERT [dbo].[AspNetRoles] ([Id], [Name], [NormalizedName], [ConcurrencyStamp]) VALUES (N'3f01bc4c-58fb-4115-a7b5-f40c5556e08a', N'Basic', N'BASIC', NULL)
INSERT [dbo].[AspNetRoles] ([Id], [Name], [NormalizedName], [ConcurrencyStamp]) VALUES (N'addd5b19-cf5d-4541-b3ba-69cc25231066', N'Premium', N'PREMIUM', NULL)
GO
INSERT [dbo].[AspNetUserRoles] ([UserId], [RoleId]) VALUES (N'64da4748-ba5e-4c35-93ef-9e6f1728f0aa', N'0f7c852c-cdcd-4124-8d4b-479103876bd7')
INSERT [dbo].[AspNetUserRoles] ([UserId], [RoleId]) VALUES (N'9d847551-dd45-4f2f-a0d1-2525043bd5db', N'0f7c852c-cdcd-4124-8d4b-479103876bd7')
INSERT [dbo].[AspNetUserRoles] ([UserId], [RoleId]) VALUES (N'0826617d-c68b-4d32-be75-bc7f703b98e4', N'3f01bc4c-58fb-4115-a7b5-f40c5556e08a')
INSERT [dbo].[AspNetUserRoles] ([UserId], [RoleId]) VALUES (N'4abd1537-9bc3-4002-bd98-8c59feaefc82', N'3f01bc4c-58fb-4115-a7b5-f40c5556e08a')
INSERT [dbo].[AspNetUserRoles] ([UserId], [RoleId]) VALUES (N'569007fd-c130-4f6a-8c4d-359d61ebc075', N'3f01bc4c-58fb-4115-a7b5-f40c5556e08a')
INSERT [dbo].[AspNetUserRoles] ([UserId], [RoleId]) VALUES (N'6d6e896f-ebb9-4a1d-bdb1-90aa22b9663a', N'3f01bc4c-58fb-4115-a7b5-f40c5556e08a')
INSERT [dbo].[AspNetUserRoles] ([UserId], [RoleId]) VALUES (N'826aab37-9293-473d-b55c-4597979bee64', N'3f01bc4c-58fb-4115-a7b5-f40c5556e08a')
INSERT [dbo].[AspNetUserRoles] ([UserId], [RoleId]) VALUES (N'92b70d77-e1f5-467e-abce-d30b1e9ca689', N'3f01bc4c-58fb-4115-a7b5-f40c5556e08a')
INSERT [dbo].[AspNetUserRoles] ([UserId], [RoleId]) VALUES (N'af2b9307-8f2e-454e-b0ad-67bb44e99e60', N'3f01bc4c-58fb-4115-a7b5-f40c5556e08a')
INSERT [dbo].[AspNetUserRoles] ([UserId], [RoleId]) VALUES (N'b12bcbfa-52bb-44bd-85b3-332834b1486d', N'3f01bc4c-58fb-4115-a7b5-f40c5556e08a')
INSERT [dbo].[AspNetUserRoles] ([UserId], [RoleId]) VALUES (N'b6c990f5-d403-4e74-a921-523d397a52f7', N'3f01bc4c-58fb-4115-a7b5-f40c5556e08a')
INSERT [dbo].[AspNetUserRoles] ([UserId], [RoleId]) VALUES (N'e696a511-df7f-44c8-8715-16453c37d968', N'3f01bc4c-58fb-4115-a7b5-f40c5556e08a')
INSERT [dbo].[AspNetUserRoles] ([UserId], [RoleId]) VALUES (N'ea630930-eed5-406e-819b-57f94187dd83', N'3f01bc4c-58fb-4115-a7b5-f40c5556e08a')
INSERT [dbo].[AspNetUserRoles] ([UserId], [RoleId]) VALUES (N'f4dc4fd3-e4bd-414c-8951-4e7a1abe6eef', N'3f01bc4c-58fb-4115-a7b5-f40c5556e08a')
INSERT [dbo].[AspNetUserRoles] ([UserId], [RoleId]) VALUES (N'f6248ee8-dce5-4fd8-bb42-a5227ce789a7', N'3f01bc4c-58fb-4115-a7b5-f40c5556e08a')
GO
INSERT [dbo].[AspNetUsers] ([Id], [Nick], [Name], [Surname1], [Surname2], [Bday], [ProfileImage], [About], [UserLocation], [PublicProfile], [UserName], [NormalizedUserName], [Email], [NormalizedEmail], [EmailConfirmed], [PasswordHash], [SecurityStamp], [ConcurrencyStamp], [PhoneNumber], [PhoneNumberConfirmed], [TwoFactorEnabled], [LockoutEnd], [LockoutEnabled], [AccessFailedCount]) VALUES (N'0826617d-c68b-4d32-be75-bc7f703b98e4', N'Repollo', N'Repollo', N'Repollo', NULL, CAST(N'3333-01-01' AS Date), N'https://88.24.26.59/imgs/default-profile.jpg', NULL, NULL, 1, N'quierounwaffle@gmail.com', N'QUIEROUNWAFFLE@GMAIL.COM', N'quierounwaffle@gmail.com', N'QUIEROUNWAFFLE@GMAIL.COM', 1, N'AQAAAAIAAYagAAAAEHj6Q7YmDRRyJCbv6eDmkxA1hmmsQbmVnkWlyigBmVudXjRUHF2fkZBWTn/XTMa84g==', N'6UV5L4CLCMWZLSIA5AKZEJ4BS3WICCWM', N'c597f9ef-14a7-4fee-addd-45544e098869', NULL, 0, 0, NULL, 1, 0)
INSERT [dbo].[AspNetUsers] ([Id], [Nick], [Name], [Surname1], [Surname2], [Bday], [ProfileImage], [About], [UserLocation], [PublicProfile], [UserName], [NormalizedUserName], [Email], [NormalizedEmail], [EmailConfirmed], [PasswordHash], [SecurityStamp], [ConcurrencyStamp], [PhoneNumber], [PhoneNumberConfirmed], [TwoFactorEnabled], [LockoutEnd], [LockoutEnabled], [AccessFailedCount]) VALUES (N'4abd1537-9bc3-4002-bd98-8c59feaefc82', N'Fae', N'Papaya', N'Pocha', NULL, CAST(N'2025-05-21' AS Date), N'https://88.24.26.59/imgs/default-profile.jpg', NULL, NULL, 0, N'hidekikazuhito@gmail.com', N'HIDEKIKAZUHITO@GMAIL.COM', N'hidekikazuhito@gmail.com', N'HIDEKIKAZUHITO@GMAIL.COM', 0, N'AQAAAAIAAYagAAAAEPMwSKo9y9NPIjQFKLPqwlCXMrDbpQWB0FTNsTtNCalNRjHURnAAXrWyUEqsFxTDaA==', N'4JAQV5YKEGHZOTGK75F5MYYFOG2XAIPP', N'561b0ddd-b457-4103-b680-626fc334e161', NULL, 0, 0, NULL, 1, 0)
INSERT [dbo].[AspNetUsers] ([Id], [Nick], [Name], [Surname1], [Surname2], [Bday], [ProfileImage], [About], [UserLocation], [PublicProfile], [UserName], [NormalizedUserName], [Email], [NormalizedEmail], [EmailConfirmed], [PasswordHash], [SecurityStamp], [ConcurrencyStamp], [PhoneNumber], [PhoneNumberConfirmed], [TwoFactorEnabled], [LockoutEnd], [LockoutEnabled], [AccessFailedCount]) VALUES (N'569007fd-c130-4f6a-8c4d-359d61ebc075', N'Lasting-Boy', N'César', N'Matelat', N'Borneo', CAST(N'1968-04-05' AS Date), N'https://88.24.26.59/imgs/profile/Lasting-Boy/Profile..jpg', N'Una Prueba Más', N'Mar del Plata', 0, N'orions68@gmail.com', N'ORIONS68@GMAIL.COM', N'orions68@gmail.com', N'ORIONS68@GMAIL.COM', 1, N'AQAAAAIAAYagAAAAEOUn3FrI37j2Hkp+PPvcuzsJqEZDHvMIqUV7gGzCGQnvX2vjj0mfJ58kOEhXOpUYOQ==', N'R4ZXAWPKA4PXU6EUHZ4BMS32QQ2KKRDV', N'475c20d1-b68a-46ae-80e2-ee297ca67817', N'664774821', 0, 0, NULL, 1, 0)
INSERT [dbo].[AspNetUsers] ([Id], [Nick], [Name], [Surname1], [Surname2], [Bday], [ProfileImage], [About], [UserLocation], [PublicProfile], [UserName], [NormalizedUserName], [Email], [NormalizedEmail], [EmailConfirmed], [PasswordHash], [SecurityStamp], [ConcurrencyStamp], [PhoneNumber], [PhoneNumberConfirmed], [TwoFactorEnabled], [LockoutEnd], [LockoutEnabled], [AccessFailedCount]) VALUES (N'64da4748-ba5e-4c35-93ef-9e6f1728f0aa', N'PatrokWanzana', N'Patrick Edward', N'Murphy', N'González', CAST(N'2001-01-03' AS Date), N'https://88.24.26.59/imgs/profile/Patrokwanzana/Patrick.jpg', N'Hola', N'Aqúiii', 1, N'patrickmurphygon@gmail.com', N'PATRICKMURPHYGON@GMAIL.COM', N'patrickmurphygon@gmail.com', N'PATRICKMURPHYGON@GMAIL.COM', 1, N'AQAAAAIAAYagAAAAEBzOlPhT9gEuujHFUhj49zekL8z9wpsk2AAWKhx68CmqjmYwNt1zVI6Kn4ckikC7oA==', N'3019317c-2504-42a3-a7e8-855857b9a9ac', N'48ff1053-a189-44d3-9e08-38761e6cc067', N'634547833', 0, 0, NULL, 0, 0)
INSERT [dbo].[AspNetUsers] ([Id], [Nick], [Name], [Surname1], [Surname2], [Bday], [ProfileImage], [About], [UserLocation], [PublicProfile], [UserName], [NormalizedUserName], [Email], [NormalizedEmail], [EmailConfirmed], [PasswordHash], [SecurityStamp], [ConcurrencyStamp], [PhoneNumber], [PhoneNumberConfirmed], [TwoFactorEnabled], [LockoutEnd], [LockoutEnabled], [AccessFailedCount]) VALUES (N'6d6e896f-ebb9-4a1d-bdb1-90aa22b9663a', N'curso.web.sce', N'Curso Aplicaciones WEB', N'', NULL, CAST(N'0001-01-01' AS Date), N'https://lh3.googleusercontent.com/a/ACg8ocKhmnOYFHsIqsY1uZUYcDMaFDPK7_hXzWAcOdNorl8n-k0SqSg=s96-c', NULL, NULL, 0, N'curso.web.sce@gmail.com', N'CURSO.WEB.SCE@GMAIL.COM', N'curso.web.sce@gmail.com', N'CURSO.WEB.SCE@GMAIL.COM', 1, N'AQAAAAIAAYagAAAAEJB+D05/ffy2HjhNOSUoFLkroe0B8ZRHLKs0m6bcNei7dlYPki2VikhIgTJw1flxrA==', N'BKWSDUS6K7ED442EGYEMYDEJZCUDFCCC', N'91765f29-6662-42ff-a867-f44a49000fa4', N'', 0, 0, NULL, 1, 0)
INSERT [dbo].[AspNetUsers] ([Id], [Nick], [Name], [Surname1], [Surname2], [Bday], [ProfileImage], [About], [UserLocation], [PublicProfile], [UserName], [NormalizedUserName], [Email], [NormalizedEmail], [EmailConfirmed], [PasswordHash], [SecurityStamp], [ConcurrencyStamp], [PhoneNumber], [PhoneNumberConfirmed], [TwoFactorEnabled], [LockoutEnd], [LockoutEnabled], [AccessFailedCount]) VALUES (N'826aab37-9293-473d-b55c-4597979bee64', N'darturosanchezg', N'Daniel Sanchez', N'', NULL, CAST(N'0001-01-01' AS Date), N'https://lh3.googleusercontent.com/a/ACg8ocJ1Q32GmOb3r8X1-UJZfikkuHH2Lt59N7Jz1KDQwIpVCgpQuA=s96-c', NULL, NULL, 0, N'darturosanchezg@gmail.com', N'DARTUROSANCHEZG@GMAIL.COM', N'darturosanchezg@gmail.com', N'DARTUROSANCHEZG@GMAIL.COM', 1, N'AQAAAAIAAYagAAAAEFZ1l9q0TB0xDlGCzJedH+ksISi+ChZBjmRt2IUnqiTKZnqnDoDLhSQ5Ozyg95CA3w==', N'ZGREA5FK5VQYOWODXKJJGVLL26BKIPRA', N'22e3bd31-a98a-4e67-bc5a-a78787e97314', N'', 0, 0, NULL, 1, 0)
INSERT [dbo].[AspNetUsers] ([Id], [Nick], [Name], [Surname1], [Surname2], [Bday], [ProfileImage], [About], [UserLocation], [PublicProfile], [UserName], [NormalizedUserName], [Email], [NormalizedEmail], [EmailConfirmed], [PasswordHash], [SecurityStamp], [ConcurrencyStamp], [PhoneNumber], [PhoneNumberConfirmed], [TwoFactorEnabled], [LockoutEnd], [LockoutEnabled], [AccessFailedCount]) VALUES (N'92b70d77-e1f5-467e-abce-d30b1e9ca689', N'QQQQQ', N'QQQQQ', N'QQQQQ', NULL, CAST(N'2025-05-23' AS Date), N'https://88.24.26.59/imgs/default-profile.jpg', NULL, NULL, 0, N'g45e6mzinc@qzueos.com', N'G45E6MZINC@QZUEOS.COM', N'g45e6mzinc@qzueos.com', N'G45E6MZINC@QZUEOS.COM', 1, N'AQAAAAIAAYagAAAAEIGkV3VeRcN6VbjDFOmc/4xJfbgae4MfZAJjI8mENIrRVrzWWqh7orRI839WXCXfAQ==', N'LZ72IJD3723HVSKRVTPAXRVNYZ6BD3UH', N'f70b966f-216c-4297-85c3-7d2f23153c0d', NULL, 0, 0, NULL, 1, 0)
INSERT [dbo].[AspNetUsers] ([Id], [Nick], [Name], [Surname1], [Surname2], [Bday], [ProfileImage], [About], [UserLocation], [PublicProfile], [UserName], [NormalizedUserName], [Email], [NormalizedEmail], [EmailConfirmed], [PasswordHash], [SecurityStamp], [ConcurrencyStamp], [PhoneNumber], [PhoneNumberConfirmed], [TwoFactorEnabled], [LockoutEnd], [LockoutEnabled], [AccessFailedCount]) VALUES (N'9d847551-dd45-4f2f-a0d1-2525043bd5db', N'Orions@68', N'César Osvaldo', N'Matelat', N'Borneo', CAST(N'1968-04-05' AS Date), N'https://88.24.26.59/imgs/profile/Orions@68/profile.jpg', N'El diablo sabe más por viejo que por diablo', N'Tenerife y el Mundo', 1, N'cesarmatelat@gmail.com', N'CESARMATELAT@GMAIL.COM', N'cesarmatelat@gmail.com', N'CESARMATELAT@GMAIL.COM', 1, N'AQAAAAIAAYagAAAAECO/+pkaGzk/YY4yrkOVIiNcpjhqe0tQ/EqE8X91+AoSqpbZV3Vj5yP2HxCdMhKAYQ==', N'1d812cd3-2b6e-4c35-b542-a74d6018a22c', N'edda5b6e-328e-448f-81a5-32bc94ff44d1', N'664774821', 0, 0, NULL, 0, 0)
INSERT [dbo].[AspNetUsers] ([Id], [Nick], [Name], [Surname1], [Surname2], [Bday], [ProfileImage], [About], [UserLocation], [PublicProfile], [UserName], [NormalizedUserName], [Email], [NormalizedEmail], [EmailConfirmed], [PasswordHash], [SecurityStamp], [ConcurrencyStamp], [PhoneNumber], [PhoneNumberConfirmed], [TwoFactorEnabled], [LockoutEnd], [LockoutEnabled], [AccessFailedCount]) VALUES (N'af2b9307-8f2e-454e-b0ad-67bb44e99e60', N'David', N'Al', N'D', NULL, CAST(N'2025-05-27' AS Date), N'https://88.24.26.59/imgs/default-profile.jpg', NULL, NULL, 0, N'deivon.al@gmail.com', N'DEIVON.AL@GMAIL.COM', N'deivon.al@gmail.com', N'DEIVON.AL@GMAIL.COM', 1, N'AQAAAAIAAYagAAAAEKyfdm8Ze3kuW352NuR6BK9Nt7YypbC9soN9hkOJYusSO1mAOOnDdmwWdx3FXH+ylw==', N'CGGIC2JE53RO65SJRT6UULKYMLWLVDS2', N'80c9c071-2b5a-49b3-b8c1-1a803f0c8abf', NULL, 0, 0, NULL, 1, 0)
INSERT [dbo].[AspNetUsers] ([Id], [Nick], [Name], [Surname1], [Surname2], [Bday], [ProfileImage], [About], [UserLocation], [PublicProfile], [UserName], [NormalizedUserName], [Email], [NormalizedEmail], [EmailConfirmed], [PasswordHash], [SecurityStamp], [ConcurrencyStamp], [PhoneNumber], [PhoneNumberConfirmed], [TwoFactorEnabled], [LockoutEnd], [LockoutEnabled], [AccessFailedCount]) VALUES (N'b12bcbfa-52bb-44bd-85b3-332834b1486d', N'Faexi', N'Lala', N'Loli', NULL, CAST(N'1993-02-22' AS Date), N'https://88.24.26.59/imgs/default-profile.jpg', N'Me gustan las patatas con kechu', N'Mi casa', 1, N'amaihideki@gmail.com', N'AMAIHIDEKI@GMAIL.COM', N'amaihideki@gmail.com', N'AMAIHIDEKI@GMAIL.COM', 1, N'AQAAAAIAAYagAAAAEEq+Gw6M23GXLZxQrvRNmrkGn9Mvt1co/Eli9+4zchOZaSr/ru8l3dI8doooEn112w==', N'PER4UF7EKYITK55I5B7VQOZTUAJK5PNA', N'9126162a-7b3c-4951-abca-1d95171a9a6b', N'644388160', 0, 0, NULL, 1, 0)
INSERT [dbo].[AspNetUsers] ([Id], [Nick], [Name], [Surname1], [Surname2], [Bday], [ProfileImage], [About], [UserLocation], [PublicProfile], [UserName], [NormalizedUserName], [Email], [NormalizedEmail], [EmailConfirmed], [PasswordHash], [SecurityStamp], [ConcurrencyStamp], [PhoneNumber], [PhoneNumberConfirmed], [TwoFactorEnabled], [LockoutEnd], [LockoutEnabled], [AccessFailedCount]) VALUES (N'b6c990f5-d403-4e74-a921-523d397a52f7', N'Papayo', N'Papayo', N'Papayo', NULL, CAST(N'2025-05-21' AS Date), N'https://88.24.26.59/imgs/default-profile.jpg', NULL, NULL, 0, N'patrickmurphy03@hotmail.es', N'PATRICKMURPHY03@HOTMAIL.ES', N'patrickmurphy03@hotmail.es', N'PATRICKMURPHY03@HOTMAIL.ES', 0, N'AQAAAAIAAYagAAAAEOa6E0+N8nZxtTFb0o2p4BtTpGdMvGISeIZgd1ozsUwKqPZE6wiX9lX5B7VWYmrbTA==', N'XCXIORMCSZC5AZ4IY4J5E42DGPEMJ6FB', N'7058c1c8-150f-4278-9afc-bba34b5ebbe0', NULL, 0, 0, NULL, 1, 0)
INSERT [dbo].[AspNetUsers] ([Id], [Nick], [Name], [Surname1], [Surname2], [Bday], [ProfileImage], [About], [UserLocation], [PublicProfile], [UserName], [NormalizedUserName], [Email], [NormalizedEmail], [EmailConfirmed], [PasswordHash], [SecurityStamp], [ConcurrencyStamp], [PhoneNumber], [PhoneNumberConfirmed], [TwoFactorEnabled], [LockoutEnd], [LockoutEnabled], [AccessFailedCount]) VALUES (N'e696a511-df7f-44c8-8715-16453c37d968', N'3.cutecovers', N'Cute Covers', N'', NULL, CAST(N'0001-01-01' AS Date), N'https://lh3.googleusercontent.com/a/ACg8ocJVVemGiTB6vfWcU21E_x9w2w8lsePQdzLd3uJlz3KJqynR4Y4=s96-c', NULL, NULL, 0, N'3.cutecovers@gmail.com', N'3.CUTECOVERS@GMAIL.COM', N'3.cutecovers@gmail.com', N'3.CUTECOVERS@GMAIL.COM', 1, N'AQAAAAIAAYagAAAAEN+PwZtP40McLDZNaklxhulVg3/XfZ3UlluUPtcJlW5Njl6Irc6w7WEDJbfbUbTiVw==', N'FXXH3C62JZKUKJP6Z6ND7MDNALHRS5NO', N'7a197c26-da5a-49f0-a8e2-eac50a21f311', N'', 0, 0, NULL, 1, 0)
INSERT [dbo].[AspNetUsers] ([Id], [Nick], [Name], [Surname1], [Surname2], [Bday], [ProfileImage], [About], [UserLocation], [PublicProfile], [UserName], [NormalizedUserName], [Email], [NormalizedEmail], [EmailConfirmed], [PasswordHash], [SecurityStamp], [ConcurrencyStamp], [PhoneNumber], [PhoneNumberConfirmed], [TwoFactorEnabled], [LockoutEnd], [LockoutEnabled], [AccessFailedCount]) VALUES (N'ea630930-eed5-406e-819b-57f94187dd83', N'javier.cpweb', N'javier romero', N'', NULL, CAST(N'0001-01-01' AS Date), N'https://lh3.googleusercontent.com/a/ACg8ocKE4r8sdERgiGIU6JKw5vB7hYtOsHf3ng02ABzTmty0T2zaTQ=s96-c', NULL, NULL, 0, N'javier.cpweb@gmail.com', N'JAVIER.CPWEB@GMAIL.COM', N'javier.cpweb@gmail.com', N'JAVIER.CPWEB@GMAIL.COM', 1, N'AQAAAAIAAYagAAAAEJMof1q+QqzPVHiS6v5YJ2jms7E5AbQbVaWEG/xjLmZFvxXSS2wG/sTERSQcNh9bwA==', N'IP3L5GGCIH4RLSMUUQ4XEWXYVDHNAD5N', N'4d269842-bf89-49b6-a0a7-ac5eaad6a8ba', N'', 0, 0, NULL, 1, 0)
INSERT [dbo].[AspNetUsers] ([Id], [Nick], [Name], [Surname1], [Surname2], [Bday], [ProfileImage], [About], [UserLocation], [PublicProfile], [UserName], [NormalizedUserName], [Email], [NormalizedEmail], [EmailConfirmed], [PasswordHash], [SecurityStamp], [ConcurrencyStamp], [PhoneNumber], [PhoneNumberConfirmed], [TwoFactorEnabled], [LockoutEnd], [LockoutEnabled], [AccessFailedCount]) VALUES (N'f4dc4fd3-e4bd-414c-8951-4e7a1abe6eef', N'matelat', N'Cesar Matelat', N'', NULL, CAST(N'0001-01-01' AS Date), N'https://lh3.googleusercontent.com/a/ACg8ocJZKO3-pdOBkUqu1uy_xiXk0TMDb48yiKsdHCMpBNLuu2Y3Ikw=s96-c', NULL, NULL, 0, N'matelat@gmail.com', N'MATELAT@GMAIL.COM', N'matelat@gmail.com', N'MATELAT@GMAIL.COM', 1, N'AQAAAAIAAYagAAAAEMTPzaKxKQSSGNeJgFv03PZT0ENYqALACfm0/Sw0SbACLQz7DZRw2rClmB1J5kQrvA==', N'QUS3MPS5BZL5OP2HYMNECDKJQJBJB5KA', N'f87fc6dc-32a1-421b-9323-df8adbcce502', N'', 0, 0, NULL, 1, 0)
INSERT [dbo].[AspNetUsers] ([Id], [Nick], [Name], [Surname1], [Surname2], [Bday], [ProfileImage], [About], [UserLocation], [PublicProfile], [UserName], [NormalizedUserName], [Email], [NormalizedEmail], [EmailConfirmed], [PasswordHash], [SecurityStamp], [ConcurrencyStamp], [PhoneNumber], [PhoneNumberConfirmed], [TwoFactorEnabled], [LockoutEnd], [LockoutEnabled], [AccessFailedCount]) VALUES (N'f6248ee8-dce5-4fd8-bb42-a5227ce789a7', N'Prueba-Imagen', N'César', N'Matelat', N'Borneo', CAST(N'1968-04-05' AS Date), N'https://88.24.26.59/imgs/profile/Prueba-Imagen/Profile..jpg', N'El Mismo de Siempre', N'Tenerife', 0, N'orions@gmx.net', N'ORIONS@GMX.NET', N'orions@gmx.net', N'ORIONS@GMX.NET', 1, N'AQAAAAIAAYagAAAAEP5qbRwQbXydWCfIaKoMgTQPlRihFDi5cdNz9k4qrRJMD7WqqJ/udfQOgaXuTXiJOQ==', N'XEJOHT76ZQJVYXFT6UKY2DBUQKZN2JJV', N'3bd22800-ebc7-42aa-ac57-407084e57d43', N'664774821', 0, 0, NULL, 1, 0)
GO
SET IDENTITY_INSERT [dbo].[Comments] ON 

INSERT [dbo].[Comments] ([Id], [UserNick], [Comment], [UserId], [ConstellationId], [ConstellationName]) VALUES (1, N'PatrokWanzana', N'PAPAPAPAPAPATATATATATATATATATTA', N'64da4748-ba5e-4c35-93ef-9e6f1728f0aa', 77, N'Serpent Cauda')
INSERT [dbo].[Comments] ([Id], [UserNick], [Comment], [UserId], [ConstellationId], [ConstellationName]) VALUES (2, N'Orions@68', N'Andrómeda Espectacular el año que viene volvemos.', N'9d847551-dd45-4f2f-a0d1-2525043bd5db', 1, N'Andromeda')
INSERT [dbo].[Comments] ([Id], [UserNick], [Comment], [UserId], [ConstellationId], [ConstellationName]) VALUES (43, N'PatrokWanzana', N'POTAJE', N'64da4748-ba5e-4c35-93ef-9e6f1728f0aa', 1, N'Andromeda')
INSERT [dbo].[Comments] ([Id], [UserNick], [Comment], [UserId], [ConstellationId], [ConstellationName]) VALUES (45, N'PatrokWanzana', N'LA HYDRA', N'64da4748-ba5e-4c35-93ef-9e6f1728f0aa', 42, N'Hydra')
INSERT [dbo].[Comments] ([Id], [UserNick], [Comment], [UserId], [ConstellationId], [ConstellationName]) VALUES (53, N'PatrokWanzana', N'Me gusta esta constelación Uwu', N'64da4748-ba5e-4c35-93ef-9e6f1728f0aa', 76, N'Serpens Caput')
INSERT [dbo].[Comments] ([Id], [UserNick], [Comment], [UserId], [ConstellationId], [ConstellationName]) VALUES (54, N'PatrokWanzana', N'Ofichuchis', N'64da4748-ba5e-4c35-93ef-9e6f1728f0aa', 59, N'Ophiuchus')
INSERT [dbo].[Comments] ([Id], [UserNick], [Comment], [UserId], [ConstellationId], [ConstellationName]) VALUES (73, N'Orions@68', N'Es mi Signo del Zodiaco', N'9d847551-dd45-4f2f-a0d1-2525043bd5db', 7, N'Aries')
INSERT [dbo].[Comments] ([Id], [UserNick], [Comment], [UserId], [ConstellationId], [ConstellationName]) VALUES (74, N'Orions@68', N'No por nada mi primera dirección de E-mail es orions@gmx.net', N'9d847551-dd45-4f2f-a0d1-2525043bd5db', 60, N'Orion')
INSERT [dbo].[Comments] ([Id], [UserNick], [Comment], [UserId], [ConstellationId], [ConstellationName]) VALUES (75, N'PatrokWanzana', N'Hola', N'64da4748-ba5e-4c35-93ef-9e6f1728f0aa', 77, N'Serpens Cauda')
INSERT [dbo].[Comments] ([Id], [UserNick], [Comment], [UserId], [ConstellationId], [ConstellationName]) VALUES (78, N'Faexi', N'Caputlines', N'b12bcbfa-52bb-44bd-85b3-332834b1486d', 76, N'Serpens Caput')
INSERT [dbo].[Comments] ([Id], [UserNick], [Comment], [UserId], [ConstellationId], [ConstellationName]) VALUES (79, N'PatrokWanzana', N'Me gusta las bolas de mono', N'64da4748-ba5e-4c35-93ef-9e6f1728f0aa', 77, N'Serpens Cauda')
SET IDENTITY_INSERT [dbo].[Comments] OFF
GO
SET IDENTITY_INSERT [dbo].[Favorites] ON 

INSERT [dbo].[Favorites] ([Id], [UserId], [ConstellationId]) VALUES (1, N'9d847551-dd45-4f2f-a0d1-2525043bd5db', 1)
INSERT [dbo].[Favorites] ([Id], [UserId], [ConstellationId]) VALUES (18, N'64da4748-ba5e-4c35-93ef-9e6f1728f0aa', 73)
INSERT [dbo].[Favorites] ([Id], [UserId], [ConstellationId]) VALUES (24, N'64da4748-ba5e-4c35-93ef-9e6f1728f0aa', 20)
INSERT [dbo].[Favorites] ([Id], [UserId], [ConstellationId]) VALUES (43, N'64da4748-ba5e-4c35-93ef-9e6f1728f0aa', 42)
INSERT [dbo].[Favorites] ([Id], [UserId], [ConstellationId]) VALUES (55, N'64da4748-ba5e-4c35-93ef-9e6f1728f0aa', 59)
INSERT [dbo].[Favorites] ([Id], [UserId], [ConstellationId]) VALUES (67, N'64da4748-ba5e-4c35-93ef-9e6f1728f0aa', 5)
INSERT [dbo].[Favorites] ([Id], [UserId], [ConstellationId]) VALUES (68, N'9d847551-dd45-4f2f-a0d1-2525043bd5db', 7)
INSERT [dbo].[Favorites] ([Id], [UserId], [ConstellationId]) VALUES (71, N'64da4748-ba5e-4c35-93ef-9e6f1728f0aa', 76)
INSERT [dbo].[Favorites] ([Id], [UserId], [ConstellationId]) VALUES (72, N'9d847551-dd45-4f2f-a0d1-2525043bd5db', 60)
INSERT [dbo].[Favorites] ([Id], [UserId], [ConstellationId]) VALUES (73, N'64da4748-ba5e-4c35-93ef-9e6f1728f0aa', 2)
INSERT [dbo].[Favorites] ([Id], [UserId], [ConstellationId]) VALUES (78, N'b12bcbfa-52bb-44bd-85b3-332834b1486d', 76)
INSERT [dbo].[Favorites] ([Id], [UserId], [ConstellationId]) VALUES (79, N'b12bcbfa-52bb-44bd-85b3-332834b1486d', 43)
INSERT [dbo].[Favorites] ([Id], [UserId], [ConstellationId]) VALUES (81, N'af2b9307-8f2e-454e-b0ad-67bb44e99e60', 77)
INSERT [dbo].[Favorites] ([Id], [UserId], [ConstellationId]) VALUES (82, N'af2b9307-8f2e-454e-b0ad-67bb44e99e60', 59)
INSERT [dbo].[Favorites] ([Id], [UserId], [ConstellationId]) VALUES (83, N'af2b9307-8f2e-454e-b0ad-67bb44e99e60', 62)
SET IDENTITY_INSERT [dbo].[Favorites] OFF
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [IX_AspNetRoleClaims_RoleId]    Script Date: 28/06/2025 9:46:16 ******/
CREATE NONCLUSTERED INDEX [IX_AspNetRoleClaims_RoleId] ON [dbo].[AspNetRoleClaims]
(
	[RoleId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [RoleNameIndex]    Script Date: 28/06/2025 9:46:16 ******/
CREATE UNIQUE NONCLUSTERED INDEX [RoleNameIndex] ON [dbo].[AspNetRoles]
(
	[NormalizedName] ASC
)
WHERE ([NormalizedName] IS NOT NULL)
WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [IX_AspNetUserClaims_UserId]    Script Date: 28/06/2025 9:46:16 ******/
CREATE NONCLUSTERED INDEX [IX_AspNetUserClaims_UserId] ON [dbo].[AspNetUserClaims]
(
	[UserId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [IX_AspNetUserLogins_UserId]    Script Date: 28/06/2025 9:46:16 ******/
CREATE NONCLUSTERED INDEX [IX_AspNetUserLogins_UserId] ON [dbo].[AspNetUserLogins]
(
	[UserId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [IX_AspNetUserRoles_RoleId]    Script Date: 28/06/2025 9:46:16 ******/
CREATE NONCLUSTERED INDEX [IX_AspNetUserRoles_RoleId] ON [dbo].[AspNetUserRoles]
(
	[RoleId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [EmailIndex]    Script Date: 28/06/2025 9:46:16 ******/
CREATE NONCLUSTERED INDEX [EmailIndex] ON [dbo].[AspNetUsers]
(
	[NormalizedEmail] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [IX_AspNetUsers_Nick]    Script Date: 28/06/2025 9:46:16 ******/
CREATE UNIQUE NONCLUSTERED INDEX [IX_AspNetUsers_Nick] ON [dbo].[AspNetUsers]
(
	[Nick] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [UserNameIndex]    Script Date: 28/06/2025 9:46:16 ******/
CREATE UNIQUE NONCLUSTERED INDEX [UserNameIndex] ON [dbo].[AspNetUsers]
(
	[NormalizedUserName] ASC
)
WHERE ([NormalizedUserName] IS NOT NULL)
WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [IX_Comments_UserId]    Script Date: 28/06/2025 9:46:16 ******/
CREATE NONCLUSTERED INDEX [IX_Comments_UserId] ON [dbo].[Comments]
(
	[UserId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
ALTER TABLE [dbo].[AspNetRoleClaims]  WITH CHECK ADD  CONSTRAINT [FK_AspNetRoleClaims_AspNetRoles_RoleId] FOREIGN KEY([RoleId])
REFERENCES [dbo].[AspNetRoles] ([Id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[AspNetRoleClaims] CHECK CONSTRAINT [FK_AspNetRoleClaims_AspNetRoles_RoleId]
GO
ALTER TABLE [dbo].[AspNetUserClaims]  WITH CHECK ADD  CONSTRAINT [FK_AspNetUserClaims_AspNetUsers_UserId] FOREIGN KEY([UserId])
REFERENCES [dbo].[AspNetUsers] ([Id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[AspNetUserClaims] CHECK CONSTRAINT [FK_AspNetUserClaims_AspNetUsers_UserId]
GO
ALTER TABLE [dbo].[AspNetUserLogins]  WITH CHECK ADD  CONSTRAINT [FK_AspNetUserLogins_AspNetUsers_UserId] FOREIGN KEY([UserId])
REFERENCES [dbo].[AspNetUsers] ([Id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[AspNetUserLogins] CHECK CONSTRAINT [FK_AspNetUserLogins_AspNetUsers_UserId]
GO
ALTER TABLE [dbo].[AspNetUserRoles]  WITH CHECK ADD  CONSTRAINT [FK_AspNetUserRoles_AspNetRoles_RoleId] FOREIGN KEY([RoleId])
REFERENCES [dbo].[AspNetRoles] ([Id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[AspNetUserRoles] CHECK CONSTRAINT [FK_AspNetUserRoles_AspNetRoles_RoleId]
GO
ALTER TABLE [dbo].[AspNetUserRoles]  WITH CHECK ADD  CONSTRAINT [FK_AspNetUserRoles_AspNetUsers_UserId] FOREIGN KEY([UserId])
REFERENCES [dbo].[AspNetUsers] ([Id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[AspNetUserRoles] CHECK CONSTRAINT [FK_AspNetUserRoles_AspNetUsers_UserId]
GO
ALTER TABLE [dbo].[AspNetUserTokens]  WITH CHECK ADD  CONSTRAINT [FK_AspNetUserTokens_AspNetUsers_UserId] FOREIGN KEY([UserId])
REFERENCES [dbo].[AspNetUsers] ([Id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[AspNetUserTokens] CHECK CONSTRAINT [FK_AspNetUserTokens_AspNetUsers_UserId]
GO
ALTER TABLE [dbo].[Comments]  WITH CHECK ADD  CONSTRAINT [FK_Comments_AspNetUsers_UserId] FOREIGN KEY([UserId])
REFERENCES [dbo].[AspNetUsers] ([Id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[Comments] CHECK CONSTRAINT [FK_Comments_AspNetUsers_UserId]
GO
ALTER TABLE [dbo].[EmailConfirmations]  WITH CHECK ADD  CONSTRAINT [FK_EmailConfirmations_AspNetUsers] FOREIGN KEY([UserId])
REFERENCES [dbo].[AspNetUsers] ([Id])
GO
ALTER TABLE [dbo].[EmailConfirmations] CHECK CONSTRAINT [FK_EmailConfirmations_AspNetUsers]
GO
ALTER TABLE [dbo].[Favorites]  WITH CHECK ADD  CONSTRAINT [FK_UserId] FOREIGN KEY([UserId])
REFERENCES [dbo].[AspNetUsers] ([Id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[Favorites] CHECK CONSTRAINT [FK_UserId]
GO
USE [master]
GO
ALTER DATABASE [NexusUsers] SET  READ_WRITE 
GO
