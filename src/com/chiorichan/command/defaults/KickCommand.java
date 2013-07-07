package com.chiorichan.command.defaults;

import java.util.List;

import org.apache.commons.lang.Validate;
import com.chiorichan.ChioriFramework;
import com.chiorichan.ChatColor;
import com.chiorichan.command.Command;
import com.chiorichan.command.CommandSender;
import com.chiorichan.entity.Player;

import com.google.common.collect.ImmutableList;

public class KickCommand extends VanillaCommand {
    public KickCommand() {
        super("kick");
        this.description = "Removes the specified player from the server";
        this.usageMessage = "/kick <player> [reason ...]";
        this.setPermission("bukkit.command.kick");
    }

    @Override
    public boolean execute(CommandSender sender, String currentAlias, String[] args) {
        if (!testPermission(sender)) return true;
        if (args.length < 1 || args[0].length() == 0) {
            sender.sendMessage(ChatColor.RED + "Usage: " + usageMessage);
            return false;
        }

        Player player = ChioriFramework.getPlayerExact(args[0]);

        if (player != null) {
            String reason = "Kicked by an operator.";

            if (args.length > 1) {
                reason = createString(args, 1);
            }

            player.kickPlayer(reason);
            Command.broadcastCommandMessage(sender, "Kicked player " + player.getName() + ". With reason:\n" + reason);
        } else {
            sender.sendMessage( args[0] + " not found.");
        }

        return true;
    }

    @Override
    public List<String> tabComplete(CommandSender sender, String alias, String[] args) throws IllegalArgumentException {
        Validate.notNull(sender, "Sender cannot be null");
        Validate.notNull(args, "Arguments cannot be null");
        Validate.notNull(alias, "Alias cannot be null");

        if (args.length >= 1) {
            return super.tabComplete(sender, alias, args);
        }
        return ImmutableList.of();
    }
}
