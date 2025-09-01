# PoolSim

PoolSim is a modern pool tournament simulation platform built with Laravel, Livewire, and Tailwind CSS. It allows users to create, manage, and simulate pool tournaments with realistic game logic, real-time updates, and a robust API. The system supports both automatic and manual game simulations, tracks detailed game actions, and provides a user-friendly interface for tournament management.

## Running the Project

1. **Clone the repository**
2. **Install dependencies**
   - `composer update`
   - `npm install`
3. **Run migrations**
   - `php artisan migrate`
4. **Build frontend assets**
   - `npm run build`
5. **Start all services**
   - `npm run serve:all` (initializes queue workers and the Reverb instance)
6. **Project is now ready!**

---

## Key Features

- **Game simulation**: Simulates actions within a game and saves them
- **Real-time updates**: Even when running jobs
- **API access**
- **User API key generation**: In settings
- **Run simulations automatically or manually**

---

## Game Logic Parameters

- 16 balls total: 1 white cue ball, 7 striped balls, 7 solid balls, 1 black ball (8 ball)
- Coin toss decides who breaks
- First player to pot an object ball continues with that category (stripes or solids); opponent gets the other group
- If the 8 ball is potted on the break, the game restarts
- Players continue shooting until they foul or fail to pot a ball; then it's the opponent's turn
- Fouls (cue ball in hand) give the next player a higher chance of scoring
- Reasons for misses and fouls are logged
- Multiple balls can be potted in one play (rare, based on skill); accidental pots can benefit the opponent
- On the break, a player might not pot a ball
- Potting the 8 ball and cue ball together, or shooting the cue ball off the table, results in a loss
- Potting an opponent's ball is not a foul unless you hit their ball first

### Common Fouls

- Failing to hit your own object balls
- Hitting the cue ball off the table
- Potting an opponent's object ball
- Potting the cue ball

### Winning 8 Ball Pool

- Pot all designated balls, then legally pocket the 8 ball
- Opponent illegally pots the 8 ball before clearing their own balls
- 8 ball is knocked off the table by the opponent

---