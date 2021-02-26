// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Javascript to handle print orders in emarking.
 *
 * @module     mod_emarking/printorders
 * @package    mod_emarking
 * @copyright  2021 Jorge Villalón <villalon@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.8
 */
define([], function () {
  return {
    init: function () {
      document.addEventListener("change", function (e) {
        var someNode = e.target.closest(".someclass")
        if (someNode) {
          alert("It changed!")
        }
      })
    },
  }
})
