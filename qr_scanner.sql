-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 02, 2025 at 11:29 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `qr_scanner`
--

-- --------------------------------------------------------

--
-- Table structure for table `authorized_codes`
--

CREATE TABLE `authorized_codes` (
  `id` int(11) NOT NULL,
  `qr_hash` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `id_number` varchar(20) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `Contact` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `authorized_codes`
--

INSERT INTO `authorized_codes` (`id`, `qr_hash`, `name`, `id_number`, `photo`, `created_at`, `Contact`) VALUES
(1, '0604029809', 'Test', '2023-00108', 'photos\\boy.jpg', '2025-05-30 15:40:14', '09453144151'),
(2, '2023-03693', 'Sheena M. Kawit', '2023-01282', '', '2025-05-31 05:58:31', '0'),
(3, '2023-00864', 'Joshua P. Baldoza', '2023-00864', '', '2025-06-02 03:19:25', '');

-- --------------------------------------------------------

--
-- Table structure for table `scan_logs`
--

CREATE TABLE `scan_logs` (
  `id` int(11) NOT NULL,
  `qr_data` varchar(255) NOT NULL,
  `status` enum('Authorized','Unauthorized') NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `direction` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scan_logs`
--

INSERT INTO `scan_logs` (`id`, `qr_data`, `status`, `timestamp`, `direction`) VALUES
(642, '0604029809', 'Authorized', '2025-06-02 09:21:47', ''),
(643, '0604029809', 'Authorized', '2025-06-02 09:21:48', 'IN'),
(644, '0604029809', 'Authorized', '2025-06-02 09:21:50', ''),
(645, '0604029809', 'Authorized', '2025-06-02 09:21:50', 'IN'),
(646, '0604029809', 'Authorized', '2025-06-02 09:21:53', ''),
(647, '0604029809', 'Authorized', '2025-06-02 09:21:53', 'IN'),
(648, '0604029809', 'Authorized', '2025-06-02 09:21:56', ''),
(649, '0604029809', 'Authorized', '2025-06-02 09:21:56', 'IN'),
(650, '0604029809', 'Authorized', '2025-06-02 09:21:58', ''),
(651, '0604029809', 'Authorized', '2025-06-02 09:21:58', 'IN'),
(652, '0604029809', 'Authorized', '2025-06-02 09:22:01', ''),
(653, '0604029809', 'Authorized', '2025-06-02 09:22:01', 'IN'),
(654, '0604029809', 'Authorized', '2025-06-02 09:22:04', ''),
(655, '0604029809', 'Authorized', '2025-06-02 09:22:04', 'IN'),
(656, '0604029809', 'Authorized', '2025-06-02 09:22:08', ''),
(657, '0604029809', 'Authorized', '2025-06-02 09:22:08', 'IN'),
(658, '0604029809', 'Authorized', '2025-06-02 09:22:11', ''),
(659, '0604029809', 'Authorized', '2025-06-02 09:22:12', 'IN'),
(660, '0604029809', 'Authorized', '2025-06-02 09:22:14', ''),
(661, '0604029809', 'Authorized', '2025-06-02 09:22:14', 'IN'),
(662, '0604029809', 'Authorized', '2025-06-02 09:22:19', ''),
(663, '0604029809', 'Authorized', '2025-06-02 09:22:19', 'IN'),
(664, '0604029809', 'Authorized', '2025-06-02 09:22:22', ''),
(665, '0604029809', 'Authorized', '2025-06-02 09:22:22', 'IN'),
(666, '0604029809', 'Authorized', '2025-06-02 09:22:39', ''),
(667, '0604029809', 'Authorized', '2025-06-02 09:22:40', 'IN'),
(668, '0604029809', 'Authorized', '2025-06-02 09:22:44', ''),
(669, '0604029809', 'Authorized', '2025-06-02 09:22:44', 'IN'),
(670, '0604029809', 'Authorized', '2025-06-02 09:22:50', ''),
(671, '0604029809', 'Authorized', '2025-06-02 09:22:50', 'IN'),
(672, '0604029809', 'Authorized', '2025-06-02 09:23:01', ''),
(673, '0604029809', 'Authorized', '2025-06-02 09:23:01', 'IN'),
(674, '0604029809', 'Authorized', '2025-06-02 09:23:04', ''),
(675, '0604029809', 'Authorized', '2025-06-02 09:23:04', 'OUT'),
(676, '0604029809', 'Authorized', '2025-06-02 09:23:08', ''),
(677, '0604029809', 'Authorized', '2025-06-02 09:23:08', 'IN'),
(678, '0604029809', 'Authorized', '2025-06-02 09:23:12', ''),
(679, '0604029809', 'Authorized', '2025-06-02 09:23:12', 'OUT'),
(680, '0604029809', 'Authorized', '2025-06-02 09:23:15', ''),
(681, '0604029809', 'Authorized', '2025-06-02 09:23:15', 'OUT'),
(682, '0604029809', 'Authorized', '2025-06-02 09:23:18', ''),
(683, '0604029809', 'Authorized', '2025-06-02 09:23:18', 'IN'),
(684, '0604029809', 'Authorized', '2025-06-02 09:23:20', ''),
(685, '0604029809', 'Authorized', '2025-06-02 09:23:20', 'IN'),
(686, '0604029809', 'Authorized', '2025-06-02 09:23:23', ''),
(687, '0604029809', 'Authorized', '2025-06-02 09:23:24', 'OUT'),
(688, '0604029809', 'Authorized', '2025-06-02 09:23:46', ''),
(689, '0604029809', 'Authorized', '2025-06-02 09:23:46', 'IN'),
(690, '0604029809', 'Authorized', '2025-06-02 09:23:49', ''),
(691, '0604029809', 'Authorized', '2025-06-02 09:23:49', 'IN'),
(692, '0604029809', 'Authorized', '2025-06-02 09:23:56', ''),
(693, '0604029809', 'Authorized', '2025-06-02 09:23:56', 'IN'),
(694, '0604029809', 'Authorized', '2025-06-02 09:23:58', ''),
(695, '0604029809', 'Authorized', '2025-06-02 09:23:59', 'IN'),
(696, '0604029809', 'Authorized', '2025-06-02 09:24:01', ''),
(697, '0604029809', 'Authorized', '2025-06-02 09:24:01', 'IN'),
(698, '0604029809', 'Authorized', '2025-06-02 09:24:09', ''),
(699, '0604029809', 'Authorized', '2025-06-02 09:24:09', 'IN'),
(700, '0604029809', 'Authorized', '2025-06-02 09:24:12', ''),
(701, '0604029809', 'Authorized', '2025-06-02 09:24:12', 'IN'),
(702, '0604029809', 'Authorized', '2025-06-02 09:24:15', ''),
(703, '0604029809', 'Authorized', '2025-06-02 09:24:15', 'IN'),
(704, '0604029809', 'Authorized', '2025-06-02 09:24:20', ''),
(705, '0604029809', 'Authorized', '2025-06-02 09:24:20', 'IN'),
(706, '0604029809', 'Authorized', '2025-06-02 09:24:35', ''),
(707, '0604029809', 'Authorized', '2025-06-02 09:24:35', 'OUT'),
(708, '0604029809', 'Authorized', '2025-06-02 09:24:39', ''),
(709, '0604029809', 'Authorized', '2025-06-02 09:24:39', 'IN'),
(710, '0604029809', 'Authorized', '2025-06-02 09:24:44', ''),
(711, '0604029809', 'Authorized', '2025-06-02 09:24:44', 'IN'),
(712, '0604029809', 'Authorized', '2025-06-02 09:28:16', ''),
(713, '0604029809', 'Authorized', '2025-06-02 09:28:16', 'IN'),
(714, '0604029809', 'Authorized', '2025-06-02 09:28:20', ''),
(715, '0604029809', 'Authorized', '2025-06-02 09:28:20', 'IN'),
(716, '0604029809', 'Authorized', '2025-06-02 09:28:23', ''),
(717, '0604029809', 'Authorized', '2025-06-02 09:28:23', 'IN'),
(718, '0604029809', 'Authorized', '2025-06-02 09:28:26', ''),
(719, '0604029809', 'Authorized', '2025-06-02 09:28:26', 'IN'),
(720, '0604029809', 'Authorized', '2025-06-02 09:28:30', ''),
(721, '0604029809', 'Authorized', '2025-06-02 09:28:30', 'IN'),
(722, '0604029809', 'Authorized', '2025-06-02 09:28:33', ''),
(723, '0604029809', 'Authorized', '2025-06-02 09:28:34', 'OUT'),
(724, '0604029809', 'Authorized', '2025-06-02 09:28:46', ''),
(725, '0604029809', 'Authorized', '2025-06-02 09:28:46', 'IN'),
(726, '0604029809', 'Authorized', '2025-06-02 09:28:51', ''),
(727, '0604029809', 'Authorized', '2025-06-02 09:28:51', 'IN'),
(728, '0604029809', 'Authorized', '2025-06-02 09:28:54', ''),
(729, '0604029809', 'Authorized', '2025-06-02 09:28:55', 'IN'),
(730, '0604029809', 'Authorized', '2025-06-02 09:28:58', ''),
(731, '0604029809', 'Authorized', '2025-06-02 09:28:58', 'IN'),
(732, '0604029809', 'Authorized', '2025-06-02 09:29:01', ''),
(733, '0604029809', 'Authorized', '2025-06-02 09:29:01', 'IN'),
(734, '0604029809', 'Authorized', '2025-06-02 09:29:05', ''),
(735, '0604029809', 'Authorized', '2025-06-02 09:29:05', 'IN');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `authorized_codes`
--
ALTER TABLE `authorized_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qr_hash` (`qr_hash`);

--
-- Indexes for table `scan_logs`
--
ALTER TABLE `scan_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_qr_data` (`qr_data`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `authorized_codes`
--
ALTER TABLE `authorized_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `scan_logs`
--
ALTER TABLE `scan_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=736;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
