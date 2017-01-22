<?php

/**
 * Class Git.php
 *
 * @author Stefano Frazzetto - https://github.com/StefanoFrazzetto
 * @version 1.1.0
 * @licence GNU AGPL v3 - https://www.gnu.org/licences/agpl-3.0.txt
 */
class Git
{
    /** @var string The current repository branch */
    private $_current_branch;

    /** Constructor */
    function __construct()
    {
        $cmd = "git branch | sed -n '/\\* /s///p'";
        $branch = trim(shell_exec($cmd));
        if (strpos($branch, "detached") !== false) {
            $this->forcePull();
        }
        $branch = trim(shell_exec($cmd));
        $this->_current_branch = $branch;
    }

    /**
     * Forces the pull from the specified branch (default is origin/master).
     *
     * @param string $branch - the branch to force pull
     */
    private function forcePull($branch = "origin/master")
    {
        exec("git fetch --all");
        exec("git reset --hard $branch");
    }

    /**
     * Returns an array containing the result associated with the flag used.
     * There is no default flag, so the default result will contain only the local arrays.
     * @link https://git-scm.com/book/en/v2/Git-Branching-Branch-Management
     *
     * @param string $flag - the flag and/or additional parameters to pass (default returns the local branches).
     * @return array - the array containing the local or remote branches.
     */
    public static function branch($flag = "")
    {
        $branches = shell_exec("git branch $flag");
        $branches = explode("\n", trim($branches));

        if ($flag != "") {
            array_shift($branches);
        }

        foreach ($branches as $key => $branch) {
            if (strpos($branch, "detached") !== false) {
                unset($branches[$key]);
            } else {
                $branches[$key] = preg_replace("/\\W+/", '', str_replace("origin/", '', $branch));
            }
        }

        return $branches;
    }

    /**
     * Changes the current branch to $branch_name forcing the checkout.
     *
     * @param string $branch_name
     * @return boolean - true on success, false otherwise.
     * @throws InvalidArgumentException if no argument is provided.
     */
    public static function checkout($branch_name = "")
    {
        if ($branch_name == "") {
            throw new InvalidArgumentException("You have to pass the target branch name.");
        }

        $res = shell_exec("git checkout $branch_name --force");
        if (strpos($res, "error") !== false) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Returns the last commits message for the current branch.
     *
     * @param int $count - the number of commits.
     * @return array - the array of commits messages.
     */
    public static function log($count = 5)
    {
        $changes = shell_exec("git log -$count --pretty=%B");
        $changes = explode("\n\n", $changes);
        array_pop($changes);

        return $changes;
    }

    /**
     * Pulls the latest changes from the current repository.
     *
     * @param string $branch - the branch where the changes will be pulled from.
     * @param bool $force - if set to true, forces the pull to the chosen branch.
     * @return bool - true if no error occurs, false otherwise.
     */
    public function pull($branch = "", $force = false)
    {
        $branch = $branch == "" ? $this->_current_branch : $branch;

        if (!$force) {
            $cmd = "git pull origin $branch";
        } else {
            $cmd = "git fetch --all && git reset --hard origin/$branch";
        }

        $res = shell_exec($cmd);
        if (strpos($res, "done") !== false || strpos($res, "up-to-date") !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if the current branch
     *
     * @return bool TRUE if is up to date and FALSE if an update is required
     * @throws Exception if cmd returns unexpected values
     */
    public function isUpToDate()
    {
        $branch = $this->getCurrentBranch();

        $result = trim(shell_exec("[ \$(git rev-parse HEAD) = \$(git ls-remote origin $branch | cut -f1) ] && echo up to date || echo not up to date"));

        switch ($result) {
            case "up to date":
                return true;
            case "not up to date":
                return false;
            default:
                throw new Exception("Invalid input returned by git cmd '$result'");
        }
    }

    /**
     * Returns the current branch.
     *
     * @return string - the current branch.
     */
    public function getCurrentBranch()
    {
        return $this->_current_branch;
    }

}