// Файл: gameModes.js

export function generateRandomBets(currentBet, sectionCoefficients, bugColors) {
    const betsForRace = [];
    const allBugs = [1, 2, 3, 4, 5, 6, 7];
    
    allBugs.forEach(bugId => {
        const randomTrapId = Math.floor(Math.random() * 7) + 1;
        betsForRace.push({
            user_id: 1,
            type: 'section',
            amount: currentBet,
            selection: [bugId],
            // --- ДОБАВЛЕНО ИСПРАВЛЕНИЕ ---
            // Бэкенд ожидает это поле с ID в формате 0-6 для корректного расчета
            bugIds: [bugId - 1], 
            // ---------------------------
            trapId: randomTrapId,
            timestamp: new Date().toISOString(),
            id: Date.now() + Math.random(),
            coefficient: sectionCoefficients?.[randomTrapId] || 1,
            bugColors: [bugColors?.[bugId - 1]]
        });
    });

    return {
        bets: betsForRace,
        totalCost: currentBet * allBugs.length
    };
}

/**
 * Создает объект ставки для режима "Favorite".
 * @param {number} currentBet - Текущая сумма ставки.
 * @param {number} favoriteBugId - ID выбранного таракана.
 * @param {Array<string>} bugColors - Массив цветов.
 * @returns {object} - Объект ставки.
 */
export function createFavoriteBet(currentBet, favoriteBugId, bugColors) {
    return {
        bugId: favoriteBugId,
        amount: currentBet,
        type: 'favorite',
        timestamp: new Date().toISOString(),
        id: Date.now() + Math.random(),
        bugColors: [bugColors[favoriteBugId - 1]]
    };
}

/**
 * Обрабатывает результат гонки для активной ставки "Favorite".
 * @param {object} activeBet - Активная ставка "Favorite".
 * @param {Array<object>} raceResults - Результаты завершившейся гонки.
 * @param {Array<string>} bugColors - Массив цветов.
 * @returns {{isWin: boolean, winAmount: number, shouldDeactivate: boolean}} - Результат для обновления состояния.
 */
export function processFavoriteBetResult(activeBet, raceResults, bugColors) {
    const bugColorOfFavorite = bugColors[activeBet.bugId - 1];
    const favoriteResult = raceResults.find(r => r.color === bugColorOfFavorite && r.position !== null);

    if (favoriteResult) {
        // ВЫИГРЫШ
        const winCoefficient = 5; // Коэффициент выигрыша, можно сделать параметром
        return {
            isWin: true,
            winAmount: activeBet.amount * winCoefficient,
            shouldDeactivate: false // Ставка продолжает действовать
        };
    } else {
        // ПРОИГРЫШ
        return {
            isWin: false,
            winAmount: 0,
            shouldDeactivate: true // Ставка сгорает
        };
    }
}