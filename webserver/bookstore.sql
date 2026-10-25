-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Εξυπηρετητής: mysql:3306
-- Χρόνος δημιουργίας: 10 Δεκ 2023 στις 14:18:18
-- Έκδοση διακομιστή: 8.0.32
-- Έκδοση PHP: 8.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Βάση δεδομένων: `bookstore`
--
CREATE DATABASE IF NOT EXISTS `bookstore` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `bookstore`;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `books`
--

CREATE TABLE `books` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `publication_year` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Άδειασμα δεδομένων του πίνακα `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `publication_year`) VALUES
(1, 'To Kill a Mockingbird', 'Harper Lee', 1960),
(2, '1984', 'George Orwell', 1949),
(3, 'The Great Gatsby', 'F. Scott Fitzgerald', 1925),
(4, 'Pride and Prejudice', 'Jane Austen', 1813),
(5, 'The Catcher in the Rye', 'J.D. Salinger', 1951),
(6, 'One Hundred Years of Solitude', 'Gabriel García Márquez', 1967),
(7, 'The Hobbit', 'J.R.R. Tolkien', 1937),
(8, 'Brave New World', 'Aldous Huxley', 1932),
(9, 'The Lord of the Rings', 'J.R.R. Tolkien', 1954),
(10, 'To the Lighthouse', 'Virginia Woolf', 1927),
(11, 'Βίος και Πολιτεία του Αλέξη Ζορμπά', 'Νίκος Καζαντζάκης', 1946),
(12, 'Ο Καπετάν Μιχάλης', 'Νίκος Καζαντζάκης', 1953),
(13, 'Η Φόνισσα', 'Αλέξανδρος Παπαδιαμάντης', 1903),
(14, 'Η Ζωή εν Τάφω', 'Στράτης Μυριβήλης', 1924),
(15, 'Αιολική Γη', 'Ηλίας Βενέζης', 1943),
(16, 'Η Μεγάλη Χίμαιρα', 'Μ. Καραγάτσης', 1953),
(17, 'Ματωμένα Χώματα', 'Διδώ Σωτηρίου', 1962),
(18, 'Το Τρίτο Στεφάνι', 'Κώστας Ταχτσής', 1962),
(19, 'The Island', 'Victoria Hislop', 2005),
(20, 'Don Quixote', 'Miguel de Cervantes', 1605),
(21, 'Frankenstein', 'Mary Shelley', 1818),
(22, 'Jane Eyre', 'Charlotte Brontë', 1847),
(23, 'Wuthering Heights', 'Emily Brontë', 1847),
(24, 'Moby-Dick', 'Herman Melville', 1851),
(25, 'Les Misérables', 'Victor Hugo', 1862),
(26, 'Crime and Punishment', 'Fyodor Dostoevsky', 1866),
(27, 'War and Peace', 'Leo Tolstoy', 1869),
(28, 'Anna Karenina', 'Leo Tolstoy', 1878),
(29, 'The Picture of Dorian Gray', 'Oscar Wilde', 1890),
(30, 'The Trial', 'Franz Kafka', 1925),
(31, 'The Stranger', 'Albert Camus', 1942),
(32, 'The Little Prince', 'Antoine de Saint-Exupéry', 1943),
(33, 'The Old Man and the Sea', 'Ernest Hemingway', 1952),
(34, 'Fahrenheit 451', 'Ray Bradbury', 1953),
(35, 'Dune', 'Frank Herbert', 1965),
(36, 'The Name of the Rose', 'Umberto Eco', 1980),
(37, 'Norwegian Wood', 'Haruki Murakami', 1987),
(38, 'The Alchemist', 'Paulo Coelho', 1988),
(39, 'Harry Potter and the Philosopher''s Stone', 'J.K. Rowling', 1997),
(40, 'The Road', 'Cormac McCarthy', 2006);

--
-- Ευρετήρια για πίνακες που εξήχθησαν
--

--
-- Ευρετήρια για πίνακα `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT για πίνακες που εξήχθησαν
--

--
-- AUTO_INCREMENT για πίνακα `books`
--
ALTER TABLE `books`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
